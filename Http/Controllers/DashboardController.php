<?php

namespace Modules\Dashboard\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $result = DB::connection('mirror')->select("WITH data_campaign AS (SELECT ID AS uid,each_data::INTEGER AS campaign,ROW_NUMBER () OVER (ORDER BY each_data) AS urut FROM core_users CROSS JOIN jsonb_array_elements (core_users.additional_data-> 'dynamicticket'-> 'escalation_campaign') each_data CROSS JOIN (SELECT :user_id AS fid) params WHERE ID=CASE WHEN fid=0 THEN ID ELSE fid END)SELECT urut,dynamicticket_escalation_campaign_id,core_user_id,CURRENT_DATE AS tanggal,COUNT (*) AS total_wo,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS submit,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.worklog @> jsonb_build_object ('followup_t2','true') THEN 1 ELSE 0 END),0) AS submit_caring,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('5') THEN 1 ELSE 0 END),0) AS submit_nossa,COALESCE (SUM (CASE WHEN (dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','MEDIACARE') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','RESOLVED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','CLOSED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','FINALCHECK') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','SALAMSIM')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS CLOSE,COALESCE (SUM (CASE WHEN (PARAMETER-> 'data' @> jsonb_build_object ('status','BACKEND') OR PARAMETER-> 'data' @> jsonb_build_object ('status','QUEUED') OR PARAMETER-> 'data' @> jsonb_build_object ('status','SLAHOLD') OR PARAMETER-> 'data' @> jsonb_build_object ('status','NEW')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS onprogress,CASE WHEN COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0)=0 THEN 0 ELSE ROUND((COALESCE (SUM (((DATE_PART('day',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at)*24+DATE_PART ('hour',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('minute',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('second',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at)),0)/(COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0)*60))::NUMERIC,2) END AS aht FROM dynamicticket_escalation_logs INNER JOIN data_campaign ON core_user_id=uid AND dynamicticket_escalation_campaign_id=campaign AND created_at::DATE=CURRENT_DATE INNER JOIN dynamicticket_datas ON dynamicticket_datas.dynamicticket_categorie_id=dynamicticket_escalation_logs.dynamicticket_categorie_id AND dynamicticket_datas.unique_key=dynamicticket_escalation_logs.unique_key GROUP BY urut,dynamicticket_escalation_campaign_id,core_user_id,tanggal", ['user_id' => Auth::id()]);
        // $result = DB::connection('mirror')->select("WITH data_campaign AS (SELECT ID AS uid,each_data::INTEGER AS campaign,ROW_NUMBER () OVER (ORDER BY each_data) AS urut FROM core_users CROSS JOIN jsonb_array_elements (core_users.additional_data-> 'dynamicticket'-> 'escalation_campaign') each_data CROSS JOIN (SELECT :user_id AS fid) params WHERE ID=CASE WHEN fid=0 THEN ID ELSE fid END)SELECT urut,dynamicticket_escalation_campaign_id,core_user_id,CURRENT_DATE AS tanggal,COUNT (*) AS total_wo,COALESCE (core_sessions.payload,'') AS payload,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS submit,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.worklog @> jsonb_build_object ('followup_t2','true') THEN 1 ELSE 0 END),0) AS submit_caring,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('5') THEN 1 ELSE 0 END),0) AS submit_nossa,COALESCE (SUM (CASE WHEN (dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','MEDIACARE') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','RESOLVED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','CLOSED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','FINALCHECK') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','SALAMSIM')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS CLOSE,COALESCE (SUM (CASE WHEN (PARAMETER-> 'data' @> jsonb_build_object ('status','BACKEND') OR PARAMETER-> 'data' @> jsonb_build_object ('status','QUEUED') OR PARAMETER-> 'data' @> jsonb_build_object ('status','SLAHOLD') OR PARAMETER-> 'data' @> jsonb_build_object ('status','NEW')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS onprogress,CASE WHEN COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0)=0 THEN 0 ELSE ROUND((COALESCE (SUM (((DATE_PART('day',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at)*24+DATE_PART ('hour',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('minute',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('second',(dynamicticket_escalation_logs.worklog-> 'status'->> '4')::TIMESTAMP-dynamicticket_escalation_logs.created_at)),0)/(COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0)*60))::NUMERIC,2) END AS aht FROM dynamicticket_escalation_logs INNER JOIN data_campaign ON core_user_id=uid AND dynamicticket_escalation_campaign_id=campaign AND created_at::DATE=CURRENT_DATE INNER JOIN dynamicticket_datas ON dynamicticket_datas.dynamicticket_categorie_id=dynamicticket_escalation_logs.dynamicticket_categorie_id AND dynamicticket_datas.unique_key=dynamicticket_escalation_logs.unique_key LEFT JOIN (SELECT*FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity=X.maxed) core_sessions ON core_sessions.user_id=dynamicticket_escalation_logs.core_user_id AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') GROUP BY urut,dynamicticket_escalation_campaign_id,core_user_id,tanggal,payload", ['user_id' => Auth::id()]);
        $container = array();
        foreach ($result as $key => $value) {
            $temp = (array) $value;
            foreach ($temp as $temp_key => $temp_value) {
                if (is_numeric($temp_value)) {
                    if (!array_key_exists($temp_key, $container)) {
                        $container[$temp_key] = 0;
                    }
                    $container[$temp_key] += $temp_value;
                }
            }
            if ($key == 0) {
                $sisa = DB::connection('mirror')->select("SELECT COALESCE(rekap->>'sisa',0) AS sisa FROM dynamicticket_rekap_campaign_daily WHERE tanggal=CURRENT_DATE AND dynamicticket_escalation_campaign_id=:escalation_campaign_id;", ['escalation_campaign_id' => $value->dynamicticket_escalation_campaign_id]);
                $container['sisa'] = $sisa[0]->sisa;
            }
        }
        return response()->json($container, 200);
    }
    public function template()
    {
        $user = json_decode(Auth::user()->additional_data);
        if (property_exists($user, 'user_nossa') && property_exists($user, 'dynamicticket') && property_exists($user->dynamicticket, 'escalation_campaign')) {
            $response = array();
            $temp = array();
            $temp['background'] = 'bg-secondary';
            $temp['icon'] = 'fas fa-database';
            $temp['label'] = 'Tickets';
            $response['sisa'] = $temp;
            $temp['background'] = 'bg-info';
            $temp['icon'] = 'far fa-envelope';
            $temp['label'] = 'Submit';
            $response['submit'] = $temp;
            $temp['background'] = 'bg-primary';
            $temp['icon'] = 'far fa-calendar-check';
            $temp['label'] = 'Consume';
            $response['submit_nossa'] = $temp;
            $temp['background'] = 'bg-success';
            $temp['icon'] = 'fas fa-flag-checkered';
            $temp['label'] = 'Close';
            $response['close'] = $temp;
            $temp['background'] = 'bg-warning';
            $temp['icon'] = 'fas fa-user-clock';
            $temp['label'] = 'AHT';
            $response['aht'] = $temp;
            return response()->json($response, 200);
        } else {
            return response()->json(false, 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('dashboard::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}