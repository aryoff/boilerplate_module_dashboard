<?php

namespace Modules\Dashboard\Http\Controllers;

use DateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

define('CAMPAIGN_T2', array(12, 13));
class DashboardT2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('dashboard::dashboardT2');
    }
    function generateListT2()
    {
        $return = '';
        foreach (CAMPAIGN_T2 as $value) {
            $return .= $value . ",";
        }
        if ($return != '') {
            $return = substr($return, 0, strlen($return) - 1);
        }
        return $return;
    }
    public function getTotalAgentOnlineT2()
    {
        $response = new \stdClass;
        $summary = array(
            'online' => 0,
            'aux' => 0,
            'aux_1' => 0,
            'aux_2' => 0,
            'aux_3' => 0,
            'aux_4' => 0,
            'aux_5' => 0,
            'aux_6' => 0,
            'aux_7' => 0,
            'aux_8' => 0,
            'aux_9' => 0,
        );
        $result = DB::connection(MIRROR)->select("SELECT core_sessions.*,X.* ,mizuvoip_cagent.agent_status,COALESCE (mizuvoip_cagent.connected_number,'') AS connected_number,mizuvoip_cagent.status_duration,COALESCE(mizuvoip_dagent.handlingtime,0)AS handlingtime,COALESCE(mizuvoip_dagent.holdtime,0)AS holdtime,COALESCE(mizuvoip_dagent.stafftime,0)AS stafftime,COALESCE(mizuvoip_dagent.total_call,0)AS total_call,core_users.NAME AS agent_name FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') AND core_sessions.last_activity=X.maxed INNER JOIN (SELECT ID,NAME FROM core_users WHERE jsonb_extract_path_text (additional_data,'dynamicticket','escalation_campaign','0') :: INTEGER IN (" . $this->generateListT2() . ")) core_users ON core_users.ID=core_sessions.user_id INNER JOIN (SELECT*FROM mizuvoip_cagent WHERE updated_at::DATE=CURRENT_DATE)mizuvoip_cagent ON core_users.ID=mizuvoip_cagent.core_user_id LEFT JOIN mizuvoip_dagent ON core_users.ID=mizuvoip_dagent.core_user_id AND mizuvoip_dagent.row_date=CURRENT_DATE"); //TODO subquery cagent harus diubah
        $data = array();
        $occ = array();
        foreach ($result as $value) {
            $temp = new \stdClass;
            $payload = (object) unserialize(unserialize(Crypt::decryptString(base64_decode($value->payload))));
            if (property_exists($payload, 'userEscalationStatus')) {
                $temp->distribution_status = $payload->userEscalationStatus;
                $total_aux = 0;
                if (property_exists($payload, 'totalAux')) {
                    $total_aux = $payload->totalAux;
                }
                if (substr($temp->distribution_status, 0, 3) == 'aux') {
                    $diff = (array) date_diff(new DateTime('now'), $payload->auxDatetime);
                    $total_aux += $diff['s'] + ($diff['i'] * 60) + ($diff['h'] * 3600);
                }
                $temp->total_aux = $total_aux;
            } else {
                $temp->distribution_status = 'offline';
            }
            $total_online = 0;
            if (property_exists($payload, 'totalOnline')) {
                $total_online += $payload->totalOnline;
            }
            if (property_exists($payload, 'onlineDatetime')) {
                $diff = (array) date_diff(new DateTime('now'), $payload->onlineDatetime);
                $total_online += $diff['s'] + ($diff['i'] * 60) + ($diff['h'] * 3600);
            }
            $temp->total_online = $total_online;
            $temp->agent_name = $value->agent_name;
            $temp->pbx_status = $value->agent_status;
            $temp->connected_number = $value->connected_number;
            $temp->status_duration = $value->status_duration;
            $temp->handlingtime = $value->handlingtime;
            $temp->holdtime = $value->holdtime;
            $temp->stafftime = $total_online;
            $temp->total_call = $value->total_call;
            if ((int)$total_online > 0) {
                $temp->occupancy = round(($value->handlingtime / $total_online) * 100, 0);
            } else {
                $temp->occupancy = 0;
            }
            $occ[$value->agent_name] = $temp->occupancy;
            $data[] = $temp;
        }
        $top_val = array();
        $top_label = array();
        $count = 0;
        ksort($occ);
        foreach ($occ as $key => $value) {
            $top_label[] = $key;
            $top_val[] = $value;
            $count++;
            if ($count >= 5) {
                break;
            }
        }
        $bot_val = array();
        $bot_label = array();
        $count = 0;
        asort($occ);
        foreach ($occ as $key => $value) {
            $bot_label[] = $key;
            $bot_val[] = $value;
            $count++;
            if ($count >= 5) {
                break;
            }
        }
        $response->data = $data;
        $response->summary = $summary;
        $response->top_value = $top_val;
        $response->top_label = $top_label;
        $response->bottom_value = $bot_val;
        $response->bottom_label = $bot_label;
        if (count($result) > 0) {
            return response()->json(json_encode($response), 200);
        } else {
            return response()->json(false, 200);
        }
    }
    public function getWaitlistT2()
    {
        $response = new \stdClass;
        $name = array();
        $count = array();
        $consumed = array();
        $totalCount = 0;
        $totalConsumed = 0;
        foreach (CAMPAIGN_T2 as $value) {
            $query = DB::connection(MIRROR)->select("SELECT parameter,name FROM dynamicticket_escalation_campaigns WHERE id = ?", [$value])[0];
            $parameter = json_decode($query->parameter);
            $filter = "SELECT * FROM dynamicticket_datas WHERE dynamicticket_categorie_id=" . $parameter->category_id; //basic default filter
            if (property_exists($parameter, 'filter')) {
                $filter = $parameter->filter;
            }
            $name[] = $query->name;
            $temp = DB::connection(MIRROR)->select("SELECT COUNT(*) AS count,SUM(CASE WHEN jsonb_exists(status, '$value') AND (status->'$value'->>'datetime')::DATE=CURRENT_DATE THEN 1 ELSE 0 END) AS consumed FROM ($filter)A;")[0];
            $count[] = $temp->count;
            $totalCount += $temp->count;
            $consumed[] = $temp->consumed;
            $totalConsumed += $temp->consumed;
        }
        $response->name = $name;
        $response->count = $count;
        $response->total_count = array($totalCount, $totalConsumed);
        $response->total_label = array('Waitlist', 'Consumed');
        return response()->json(json_encode($response), 200);
    }
}