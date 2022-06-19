<?php

namespace Modules\Dashboard\Http\Controllers;

use DateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

define('CAMPAIGN_C4', array(2, 3, 4, 5, 9, 14));

class DashboardC4Controller extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('dashboard::dashboardC4');
    }
    public function get_data_campaign()
    {
        $campaign_c4 = CAMPAIGN_C4;
        $data = array();
        $temp = new \stdClass;
        $temp->nama = 'no data';
        $temp->total = 0;
        $temp->sisa = 0;
        $temp->assigned = 0;
        $temp->pickup = 0;
        $temp->submit = 0;
        $temp->queued = 0;
        foreach ($campaign_c4 as $value) {
            $temp_result = DB::connection('mirror')->select("SELECT dynamicticket_escalation_campaigns.name AS nama,rekap FROM dynamicticket_rekap_campaign_daily INNER JOIN dynamicticket_escalation_campaigns ON dynamicticket_escalation_campaigns.id=dynamicticket_rekap_campaign_daily.dynamicticket_escalation_campaign_id WHERE tanggal=CURRENT_DATE AND dynamicticket_escalation_campaign_id=:escalation_campaign_id", ['escalation_campaign_id' => $value]);
            if (count($temp_result) > 0) {
                $temp = json_decode($temp_result[0]->rekap);
                $temp->nama = $temp_result[0]->nama;
                $result = DB::connection('mirror')->select("SELECT core_sessions.*,X.* FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') AND core_sessions.last_activity=X.maxed INNER JOIN core_users ON core_users.ID=core_sessions.user_id INNER JOIN(SELECT DISTINCT core_user_id,dynamicticket_escalation_campaign_id FROM dynamicticket_escalation_logs WHERE created_at::DATE=CURRENT_DATE)dynamicticket_escalation_logs ON dynamicticket_escalation_logs.core_user_id = core_users.ID AND core_users.additional_data->'dynamicticket'->'escalation_campaign'@>(dynamicticket_escalation_logs.dynamicticket_escalation_campaign_id::VARCHAR)::JSONB WHERE core_users.additional_data->'dynamicticket'->'escalation_campaign'@>(:campaign::VARCHAR)::JSONB;", ['campaign' => $value]);
                $staffed = 0;
                foreach ($result as $value) {
                    if ($value->payload != '') {
                        $payload = (object) unserialize(unserialize(Crypt::decryptString(base64_decode($value->payload))));
                        if (property_exists($payload, 'userEscalationStatus')) {
                            $staffed++;
                        }
                    }
                }
                $temp->staffed = $staffed;
                $data[] = $temp;
            }
        }
        $response = array(
            'data' => $data,
        );
        return response()->json($response, 200);
    }
    public function get_last_update_nossa()
    {
        return response()->json(DB::select("SELECT (DATE_PART('day',NOW()-last_update)*24+DATE_PART ('hour',NOW()-last_update))*60+DATE_PART ('minute',NOW()-last_update) AS delta_last_update,to_char(last_update,'YYYY-MM-DD')AS hari,to_char(last_update,'HH24:MI:SS')AS jam FROM (SELECT (extra_field-> 'unique_key'->> 'last_update')::TIMESTAMP AS last_update FROM dynamicticket_categories WHERE ID=2) dynamicticket_categories;")[0], 200);
    }
    public function get_realtime_staff()
    {
        $result = DB::connection('mirror')->select("SELECT core_users.name,dynamicticket_escalation_campaigns.name AS campaign_name,COALESCE (core_sessions.payload,'') AS payload,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS consume,COALESCE (SUM (CASE WHEN (dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','MEDIACARE') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','RESOLVED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','CLOSED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','FINALCHECK') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','SALAMSIM')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS close,COALESCE (SUM (CASE WHEN (dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','BACKEND') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','QUEUED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','SLAHOLD') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','NEW')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS onprogress,COALESCE (SUM (((DATE_PART('day',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at)*24+DATE_PART ('hour',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('minute',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('second',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at)),0) AS handling_time,COALESCE(pickup,'00:00:00') AS pickup,CASE WHEN tiket IS NULL THEN '-' ELSE CONCAT(tiket,'[',ticket_status,']') END AS pticket FROM dynamicticket_escalation_logs INNER JOIN dynamicticket_escalation_campaigns ON dynamicticket_escalation_campaigns.id=dynamicticket_escalation_logs.dynamicticket_escalation_campaign_id INNER JOIN core_users ON core_users.ID=dynamicticket_escalation_logs.core_user_id INNER JOIN dynamicticket_datas ON dynamicticket_datas.dynamicticket_categorie_id=dynamicticket_escalation_logs.dynamicticket_categorie_id AND dynamicticket_datas.unique_key=dynamicticket_escalation_logs.unique_key LEFT JOIN (SELECT*FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity=X.maxed) core_sessions ON core_sessions.user_id=dynamicticket_escalation_logs.core_user_id AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') LEFT JOIN (SELECT dynamicticket_escalation_logs.unique_key->>'incident' AS tiket,core_user_id AS uid,CONCAT(CASE WHEN DATE_PART('day',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))!=0 THEN DATE_PART('day',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))END,CASE WHEN DATE_PART('day',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))!=0 THEN 'd ' END,CASE WHEN DATE_PART('hour',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))<10 THEN '0' END,DATE_PART('hour',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at)),':',CASE WHEN DATE_PART('minute',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))<10 THEN '0' END,DATE_PART('minute',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at)),':',CASE WHEN FLOOR(DATE_PART('second',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at)))<10 THEN '0' END,FLOOR(DATE_PART('second',CURRENT_TIMESTAMP-MIN(dynamicticket_escalation_logs.updated_at))))AS pickup,dynamicticket_datas.PARAMETER->'data'->>'status' AS ticket_status,dynamicticket_escalation_campaign_id AS campaign_id FROM dynamicticket_escalation_logs INNER JOIN dynamicticket_datas ON dynamicticket_datas.dynamicticket_categorie_id=dynamicticket_escalation_logs.dynamicticket_categorie_id AND dynamicticket_datas.unique_key=dynamicticket_escalation_logs.unique_key WHERE dynamicticket_escalation_logs.created_at::DATE=CURRENT_DATE AND dynamicticket_escalation_logs.status='2' AND dynamicticket_escalation_logs.worklog !=jsonb_build_object('escalation','stagnate')GROUP BY dynamicticket_escalation_logs.unique_key->>'incident',core_user_id,ticket_status,dynamicticket_escalation_campaign_id) pickup_ticket ON pickup_ticket.uid=dynamicticket_escalation_logs.core_user_id AND pickup_ticket.campaign_id=dynamicticket_escalation_logs.dynamicticket_escalation_campaign_id WHERE dynamicticket_escalation_logs.created_at :: DATE=CURRENT_DATE AND dynamicticket_escalation_logs.dynamicticket_escalation_campaign_id IN (2,3,4,5,9,14) GROUP BY core_users.name,dynamicticket_escalation_campaigns.name,core_sessions.payload,pickup_ticket.pickup,pickup_ticket.tiket,pickup_ticket.ticket_status;");
        $data = array();
        foreach ($result as $value) {
            $temp = new \stdClass;
            $temp->name = $value->name;
            $temp->campaign_name = $value->campaign_name;
            $temp->consume = $value->consume;
            $temp->close = $value->close;
            $temp->onprogress = $value->onprogress;
            $temp->handling_time = $value->handling_time;
            $temp->pickup = $value->pickup;
            $temp->pticket = $value->pticket;
            $temp->total_aux = 0;
            $temp->total_online = 0;
            if ($value->payload == '') {
                $temp->status = 'offline';
            } else {
                $payload = (object) unserialize(unserialize(Crypt::decryptString(base64_decode($value->payload))));
                if (property_exists($payload, 'userEscalationStatus')) {
                    $temp->status = $payload->userEscalationStatus;
                    $total_aux = 0;
                    if (property_exists($payload, 'totalAux')) {
                        $total_aux = $payload->totalAux;
                    }
                    if (substr($temp->status, 0, 3) == 'aux') {
                        $diff = (array) date_diff(new DateTime('now'), $payload->auxDatetime);
                        $total_aux += $diff['s'] + ($diff['i'] * 60) + ($diff['h'] * 3600);
                    }
                    $temp->total_aux = $total_aux;
                } else {
                    $temp->status = 'offline';
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
            }
            $data[] = $temp;
        }
        $response = array(
            'data' => $data,
        );
        return response()->json($response, 200);
    }
    public function get_total_agent_online()
    {
        $campaign_c4 = CAMPAIGN_C4;
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
        $campaign_filter = '';
        foreach ($campaign_c4 as $value) {
            $campaign_filter .= "core_users.additional_data->'dynamicticket'->'escalation_campaign'@>(" . $value . "::VARCHAR)::JSONB OR ";
        }
        $campaign_filter = substr($campaign_filter, 0, strlen($campaign_filter) - 4);
        if ($campaign_filter != '') {
            $campaign_filter = "AND(" . $campaign_filter . ")";
        }
        $result = DB::connection('mirror')->select("SELECT core_sessions.*,X.* FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') AND core_sessions.last_activity=X.maxed INNER JOIN core_users ON core_users.ID=core_sessions.user_id INNER JOIN(SELECT DISTINCT core_user_id,dynamicticket_escalation_campaign_id FROM dynamicticket_escalation_logs WHERE created_at::DATE=CURRENT_DATE)dynamicticket_escalation_logs ON dynamicticket_escalation_logs.core_user_id = core_users.ID AND core_users.additional_data->'dynamicticket'->'escalation_campaign'@>(dynamicticket_escalation_logs.dynamicticket_escalation_campaign_id::VARCHAR)::JSONB WHERE 1=1 $campaign_filter");
        foreach ($result as $value) {
            $payload = (object) unserialize(unserialize(Crypt::decryptString(base64_decode($value->payload))));
            if (property_exists($payload, 'userEscalationStatus')) {
                $status = $payload->userEscalationStatus;
                foreach ($summary as $key => $value) {
                    if ($key == $status || $key == substr($status, 0, 3)) {
                        $summary[$key]++;
                    }
                }
            }
        }
        return response()->json($summary, 200);
    }
}