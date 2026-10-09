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


//后台接口
$router->group(['prefix' => 'manage/o2o', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //门店管理
    $router->get('/chainBase/list', 'ChainBaseController@list');
    $router->post('/chainBase/add', 'ChainBaseController@add');
    $router->post('/chainBase/edit', 'ChainBaseController@edit');
    $router->post('/chainBase/remove', 'ChainBaseController@remove');

    //门店状态切换
    $router->post('/chainBase/editState', 'ChainBaseController@editState');

    //门店商品
    $router->get('/chainItem/list', 'ChainItemController@list');
    $router->post('/chainItem/add', 'ChainItemController@add');
    $router->post('/chainItem/edit', 'ChainItemController@edit');
    $router->post('/chainItem/remove', 'ChainItemController@remove');
    $router->post('/chainItem/editState', 'ChainItemController@editState');

    //门店订单 [本地补齐 2026-09-22]
    //复用 Trade 模块 OrderService，仅按 trade_order_info.chain_id 做门店维度筛选
    $router->get('/chainOrder/list', 'ChainOrderController@list');        //门店订单列表
    $router->get('/chainOrder/detail', 'ChainOrderController@detail');    //门店订单详情
    $router->post('/chainOrder/cancel', 'ChainOrderController@cancel');   //门店订单取消

    //门店用户管理（原路由指向尚未发布的 ChainUserController，会导致 500，暂注释；
    //如需启用请先补齐 Modules\O2o\Http\Controllers\Manage\ChainUserController）
    //$router->get('/chainUser/list', 'ChainUserController@list');
    //$router->post('/chainUser/add', 'ChainUserController@add');
    //$router->post('/chainUser/edit', 'ChainUserController@edit');
    //$router->post('/chainUser/remove', 'ChainUserController@remove');

});


//前端接口
$router->group(['prefix' => 'front/o2o', 'namespace' => 'Front', 'middleware' => ['auth']], function () use ($router) {

    //门店
    $router->get('/chain/list', 'ChainController@list');

});
