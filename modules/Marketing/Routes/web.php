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


/**
 * Front 请求路径 No Auth
 */
$router->group(['prefix' => 'front/marketing','namespace'=>'Front'], function () use ($router) {

    $router->get('/activityBase/listVoucher','ActivityController@listVoucher'); //优惠券列表

});


/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/marketing','namespace'=>'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //活动管理
    $router->get('/activityBase/list','ActivityBaseController@list');
    $router->post('/activityBase/add','ActivityBaseController@add');
    $router->post('/activityBase/edit','ActivityBaseController@edit');
    $router->post('/activityBase/remove','ActivityBaseController@remove');
    $router->post('/activityBase/editState','ActivityBaseController@editState');

    //活动类型字典 [本地补齐] 厂商无路由，前端表单下拉需要
    $router->get('/activityType/list','ActivityTypeController@list');

    //活动商品
    $router->get('/activityBase/getActivityBuyItems','ActivityItemController@getActivityBuyItems');
    $router->post('/activityBase/addActivityBuyItems','ActivityItemController@addActivityBuyItems');
    $router->post('/activityBase/removeActivityBuyItems','ActivityItemController@removeActivityBuyItems');
    $router->post('/activityBase/editActivityItem','ActivityItemController@editActivityItem');
    $router->post('/activityBase/editBatchPrice','ActivityItemController@editBatchPrice');

});
