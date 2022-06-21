<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'dashboard'], function () {
    Route::get('/', 'DashboardController@index');
    Route::get('/template', 'DashboardController@template');
    Route::get('/DashboardT2', 'DashboardT2Controller@index')->name('DashboardT2');
    Route::get('/getTotalAgentOnlineT2', 'DashboardT2Controller@getTotalAgentOnlineT2');
    Route::get('/getWaitlistT2', 'DashboardT2Controller@getWaitlistT2');
    Route::get('/DashboardC4', 'DashboardC4Controller@index')->name('DashboardC4');
    Route::get('/getRealtimeStaffC4', 'DashboardC4Controller@get_realtime_staff');
    Route::get('/getTotalAgentOnlineC4', 'DashboardC4Controller@get_total_agent_online');
    Route::get('/getLastUpdateNossaC4', 'DashboardC4Controller@get_last_update_nossa');
    Route::get('/getDataCampaignC4', 'DashboardC4Controller@get_data_campaign');
});