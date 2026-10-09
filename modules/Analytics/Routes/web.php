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

// [安全修复] 原为 ['prefix' => 'manage/analytics', 'namespace' => 'Manage']，漏挂 auth。
// 结果：未登录即可访问全部统计接口（实测 /manage/analytics/order/getDashboardTimeLine 返回 200 真实数据）。
// 项目内其它 manage/* 组均已挂 auth，此处补齐，恢复一致性。
$router->group(['prefix' => 'manage/analytics', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    $router->get('/user/getVisitor', 'AnalyticsController@getVisitor');
    $router->get('/user/getRegUser', 'AnalyticsController@getRegUser');
    $router->get('/user/getUserTimeLine', 'AnalyticsController@getUserTimeLine');
    $router->get('/user/getUserNum', 'AnalyticsController@getUserNum');

    $router->get('/history/getAccessNum', 'AnalyticsController@getAccessNum');
    $router->get('/history/getAccessVisitorTimeLine', 'AnalyticsController@getAccessVisitorTimeLine');
    $router->get('/history/getAccessVisitorNum', 'AnalyticsController@getAccessVisitorNum');
    $router->get('/history/getAccessItemUserTimeLine', 'AnalyticsController@getAccessItemUserTimeLine');
    $router->get('/history/getAccessItemNum', 'AnalyticsController@getAccessItemNum');
    $router->get('/history/getAccessItemUserNum', 'AnalyticsController@getAccessItemUserNum');
    $router->get('/history/listAccessItem', 'AnalyticsController@listAccessItem');
    $router->get('/history/getAccessItemTimeLine', 'AnalyticsController@getAccessItemTimeLine');

    $router->get('/order/getOrderNum', 'AnalyticsController@getOrderNum');
    $router->get('/order/getOrderAmount', 'AnalyticsController@getOrderAmount');
    $router->get('/order/getSaleOrderAmount', 'AnalyticsController@getSaleOrderAmount');
    $router->get('/order/getOrderNumTimeline', 'AnalyticsController@getOrderNumTimeline');
    $router->get('/order/getOrderItemNum', 'AnalyticsController@getOrderItemNum');
    $router->get('/order/listOrderItemNum', 'AnalyticsController@listOrderItemNum');
    $router->get('/order/getOrderItemNumTimeLine', 'AnalyticsController@getOrderItemNumTimeLine');

    $router->get('/return/getReturnNum', 'AnalyticsReturnController@getReturnNum');
    $router->get('/return/getReturnAmount', 'AnalyticsReturnController@getReturnAmount');
    $router->get('/return/getReturnAmountTimeline', 'AnalyticsReturnController@getReturnAmountTimeline');
    $router->get('/return/getReturnNumTimeline', 'AnalyticsReturnController@getReturnNumTimeline');

    $router->get('/product/getProductNum', 'AnalyticsController@getProductNum');


    $router->get('/trade/getSalesAmount', 'AnalyticsController@getSalesAmount');
    $router->get('/order/getDashboardTimeLine', 'AnalyticsController@getDashboardTimeLine');
    $router->get('/order/getCustomerTimeline', 'AnalyticsController@getCustomerTimeline');
    $router->get('/order/getOrderNumToday', 'AnalyticsController@getOrderNumToday');
    $router->get('/order/getOrderCustomerNumTimeline', 'AnalyticsController@getOrderCustomerNumTimeline');


});

