<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

define('CAMPAIGN_T2', array(13));
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
        $result = DB::connection('mirror')->select("SELECT core_sessions.*,X.* ,mizuvoip_cagent.agent_status,COALESCE (mizuvoip_cagent.connected_number,'') AS connected_number,mizuvoip_cagent.status_duration,mizuvoip_dagent.handlingtime,mizuvoip_dagent.holdtime,mizuvoip_dagent.stafftime,mizuvoip_dagent.total_call,core_users.NAME AS agent_name FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') AND core_sessions.last_activity=X.maxed INNER JOIN (SELECT ID,NAME FROM core_users WHERE jsonb_extract_path_text (additional_data,'dynamicticket','escalation_campaign','0') :: INTEGER IN (" . $this->generateListT2() . ")) core_users ON core_users.ID=core_sessions.user_id INNER JOIN mizuvoip_cagent ON core_users.ID=mizuvoip_cagent.core_user_id INNER JOIN mizuvoip_dagent ON core_users.ID=mizuvoip_dagent.core_user_id AND mizuvoip_dagent.row_date=CURRENT_DATE");
        $data = array();
        foreach ($result as $value) {
            $payload = (object) unserialize(unserialize(Crypt::decryptString(base64_decode($value->payload))));
            if (property_exists($payload, 'userEscalationStatus')) { //ini payload isinya semua data session
                $status = $payload->userEscalationStatus;
                foreach ($summary as $key => $value) {
                    if ($key == $status || $key == substr($status, 0, 3)) {
                        $summary[$key]++;
                    }
                }
            }
            $temp = new \stdClass;
            $temp->agent_name = $value->agent_name;
            $temp->agent_status = $value->agent_status;
            $temp->connected_number = $value->connected_number;
            $temp->status_duration = $value->status_duration;
            $temp->handlingtime = $value->handlingtime;
            $temp->holdtime = $value->holdtime;
            $temp->stafftime = $value->stafftime;
            $temp->total_call = $value->total_call;
            $data[] = $temp;
        }
        $response->data = $data;
        $response->summary = $summary;
        if (count($result) > 0) {
            return response()->json(json_encode($response), 200);
        } else {
            return response()->json(false, 200);
        }
    }
    public function getWaitlistT2()
    {
        $response = array();
        foreach (CAMPAIGN_T2 as $value) {
            $query = DB::select("SELECT parameter,name FROM dynamicticket_escalation_campaigns WHERE id = ?", [$value])[0];
            $filter = "SELECT * FROM dynamicticket_datas WHERE dynamicticket_categorie_id=" . $query->parameter->category_id; //basic default filter
            if (property_exists($query->parameter, 'filter')) {
                $filter = $query->parameter->filter;
            }
            $temp = new \stdClass;
            $temp->name = $query->name;
            $temp->count = DB::select("SELECT COUNT(*) AS count FROM ($filter)A;");
            $response[] = $temp;
        }
        return response()->json(json_encode($response), 200);
    }
}