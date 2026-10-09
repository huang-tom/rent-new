<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// [安全修复] 原为 ['prefix' => 'manage/live', 'namespace' => 'Manage']，漏挂 auth。
// 结果：未登录即可读取主播申请列表（含用户姓名/手机号等个人数据）与直播间商品。
// 项目内其它 manage/* 组均已挂 auth，此处补齐。
$router->group(['prefix' => 'manage/live', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    $router->get('/userApply/list', 'UserApplyController@list'); //主播申请列表

    //直播商品管理
    $router->get('/wxLive/getApproved', 'WxLiveController@getApproved');
});
