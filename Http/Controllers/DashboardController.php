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
        return response()->json(DB::select("WITH data_campaign AS (SELECT ID AS uid,each_data :: INTEGER AS campaign,ROW_NUMBER () OVER (ORDER BY each_data) AS urut FROM core_users CROSS JOIN jsonb_array_elements (core_users.additional_data-> 'dynamicticket'-> 'escalation_campaign') each_data WHERE ID=:user_id)SELECT urut,campaign,COUNT (*) AS total_wo,COALESCE (core_sessions.payload,'') AS payload,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS submit,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.worklog @> jsonb_build_object ('followup_t2','true') THEN 1 ELSE 0 END),0) AS submit_caring,COALESCE (SUM (CASE WHEN dynamicticket_escalation_logs.status IN ('5') THEN 1 ELSE 0 END),0) AS submit_nossa,COALESCE (SUM (CASE WHEN (dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','MEDIACARE') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','RESOLVED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','CLOSED') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','FINALCHECK') OR dynamicticket_datas.PARAMETER-> 'data' @> jsonb_build_object ('status','SALAMSIM')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS CLOSE,COALESCE (SUM (CASE WHEN (PARAMETER-> 'data' @> jsonb_build_object ('status','BACKEND') OR PARAMETER-> 'data' @> jsonb_build_object ('status','QUEUED') OR PARAMETER-> 'data' @> jsonb_build_object ('status','SLAHOLD') OR PARAMETER-> 'data' @> jsonb_build_object ('status','NEW')) AND dynamicticket_escalation_logs.status IN ('4','5') THEN 1 ELSE 0 END),0) AS onprogress,COALESCE (SUM (((DATE_PART('day',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at)*24+DATE_PART ('hour',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('minute',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at))*60+DATE_PART ('second',(dynamicticket_escalation_logs.worklog-> 'status'->> '4') :: TIMESTAMP-dynamicticket_escalation_logs.created_at)),0) AS handling_time FROM dynamicticket_escalation_logs INNER JOIN data_campaign ON core_user_id=uid AND dynamicticket_escalation_campaign_id=campaign AND created_at :: DATE=CURRENT_DATE INNER JOIN dynamicticket_datas ON dynamicticket_datas.dynamicticket_categorie_id=dynamicticket_escalation_logs.dynamicticket_categorie_id AND dynamicticket_datas.unique_key=dynamicticket_escalation_logs.unique_key LEFT JOIN (SELECT*FROM core_sessions INNER JOIN (SELECT user_id AS uid,MAX (last_activity) AS maxed FROM core_sessions WHERE user_id IS NOT NULL GROUP BY user_id) X ON core_sessions.user_id=X.uid AND core_sessions.last_activity=X.maxed) core_sessions ON core_sessions.user_id=dynamicticket_escalation_logs.core_user_id AND core_sessions.last_activity> EXTRACT (EPOCH FROM NOW()-INTERVAL '120 MINUTES') GROUP BY urut,campaign,payload", ['user_id' => Auth::id()])[0], 200);
    }
    public function template()
    {
        $user = json_decode(Auth::user()->additional_data);
        if (property_exists($user, 'user_nossa')) {
            $response = array();
            $temp = array();
            $temp['background'] = 'bg-info';
            $temp['icon'] = 'far fa-envelope';
            $temp['label'] = 'Submit';
            $response['submit'] = $temp;
            $temp['background'] = 'bg-primary';
            $temp['icon'] = 'far fa-envelope';
            $temp['label'] = 'Submit Nossa';
            $response['submit_nossa'] = $temp;
            $temp['background'] = 'bg-success';
            $temp['icon'] = 'far fa-envelope';
            $temp['label'] = 'Close';
            $response['close'] = $temp;
            $temp['background'] = 'bg-warning';
            $temp['icon'] = 'far fa-envelope';
            $temp['label'] = 'Open';
            $response['onprogress'] = $temp;

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