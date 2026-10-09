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
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/trade', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    $router->get('/orderBase/list', 'OrderBaseController@list');     //订单列表
    $router->get('/orderBase/detail', 'OrderBaseController@detail'); //订单详情
    $router->get('/orderBase/listStateLog', 'OrderBaseController@listStateLog'); //订单状态日志
    $router->post('/orderBase/add', 'OrderBaseController@add');      // [新增 2026-09-22] 后台代客下单：列表「添加」按钮一直指向这里，原先未注册
    $router->post('/orderBase/editShoppingFee', 'OrderBaseController@editShoppingFee'); // [新增 2026-09-22] 修改运费，原先未注册
    $router->post('/orderBase/review', 'OrderBaseController@review');      //订单审核
    $router->post('/orderBase/finance', 'OrderBaseController@finance');    //财务审核
    $router->post('/orderBase/picking', 'OrderBaseController@picking');    //出库操作
    $router->post('/orderBase/shipping', 'OrderBaseController@shipping');  //发货操作
    $router->post('/orderBase/receive', 'OrderBaseController@receive');    //收货操作
    $router->post('/orderBase/cancel', 'OrderBaseController@cancel');      //取消订单

    // 取货码（虚拟核销码）查询
    // [新增 2026-09-22] 订单列表的取货码搜索一直在调 GET /manage/trade/chainCode/list，原先未注册
    $router->get('/chainCode/list', 'ChainCodeController@list');

    // ⚠️ 关于 /orderBase/transferToSupplier（转单给供应商）：**有意不实现**。
    //    前端 api/trade/orderBase.ts 声明了它，按钮在 views/trade/orderBase/index.vue:326，
    //    但显隐条件是 `row.has_supplier_trans == 0`，而 orderBase/list 从不返回该字段
    //    （全项目检索 has_supplier_trans：后端零引用）→ undefined == 0 为 false，按钮永远不渲染。
    //    更根本的是本版本没有任何供应商体系（无供应商表、无 supplier 相关列）
    //    凭空实现等于发明一个业务功能，不属于"修断链"。详见本轮交付文档。
    $router->post('/orderLogistics/add', 'OrderLogisticsController@add');   //订单物流 [已补方法，原为静默 404]

    $router->post('/orderLogistics/edit', 'OrderLogisticsController@edit'); //修改订单物流

    $router->get('/orderReturn/list', 'OrderReturnController@list'); //售后订单
    $router->get('/orderReturn/getByReturnId', 'OrderReturnController@getByReturnId'); //售后订单详情
    $router->post('/orderReturn/add', 'OrderReturnController@add');   // [新增 2026-09-22] 后台代建退单，原先未注册
    $router->post('/orderReturn/edit', 'OrderReturnController@edit'); // [新增 2026-09-22] 修改退款金额，原先未注册
    $router->post('/orderReturn/review', 'OrderReturnController@review'); //售后订单审核
    $router->post('/orderReturn/refused', 'OrderReturnController@refused'); //拒绝退单
    $router->post('/orderReturn/receive', 'OrderReturnController@receive'); //确认收货
    $router->post('/orderReturn/refund', 'OrderReturnController@refund'); //确认付款

    //退款原因
    $router->get('/orderReturnReason/list', 'OrderReturnReasonController@list');
    $router->post('/orderReturnReason/add', 'OrderReturnReasonController@add');
    $router->post('/orderReturnReason/edit', 'OrderReturnReasonController@edit');
    $router->post('/orderReturnReason/remove', 'OrderReturnReasonController@remove'); // [新增 2026-09-22] 控制器方法早就有了，只是漏了这条路由

    //订单发票管理
    $router->get('/orderInvoice/list', 'OrderInvoiceController@list');
    $router->post('/orderInvoice/add', 'OrderInvoiceController@add');       // [新增 2026-09-22] 原先未注册
    $router->post('/orderInvoice/edit', 'OrderInvoiceController@edit');     // [新增 2026-09-22] 原先未注册
    $router->post('/orderInvoice/remove', 'OrderInvoiceController@remove'); // [新增 2026-09-22] 原先未注册
    $router->post('/orderInvoice/editStatus', 'OrderInvoiceController@editStatus');

    //推广订单列表
    $router->get('/distributionOrder/list', 'DistributionOrderController@list');

    //新购买订单（与旧订单隔离）
    $router->get('/newOrder/list', 'NewOrderController@list');
    $router->get('/newOrder/get', 'NewOrderController@get');
    $router->post('/newOrder/add', 'NewOrderController@add');
    $router->post('/newOrder/assignWarehouse', 'NewOrderController@assignWarehouse');
    $router->post('/newOrder/addTag', 'NewOrderController@addTag');
    $router->post('/newOrder/removeTag', 'NewOrderController@removeTag');
    $router->post('/newOrder/addRemark', 'NewOrderController@addRemark');
    $router->post('/newOrder/refund', 'NewOrderController@refund');

    //新租赁订单（与旧订单 / 新购买订单隔离）
    $router->get('/newRentOrder/list', 'NewRentOrderController@list');
    $router->get('/newRentOrder/get', 'NewRentOrderController@get');
    $router->post('/newRentOrder/add', 'NewRentOrderController@add');
    $router->post('/newRentOrder/assignWarehouse', 'NewRentOrderController@assignWarehouse');
    $router->post('/newRentOrder/addTag', 'NewRentOrderController@addTag');
    $router->post('/newRentOrder/removeTag', 'NewRentOrderController@removeTag');
    $router->post('/newRentOrder/addRemark', 'NewRentOrderController@addRemark');
    $router->post('/newRentOrder/refund', 'NewRentOrderController@refund');
    $router->post('/newRentOrder/returnRent', 'NewRentOrderController@returnRent');
    $router->post('/newRentOrder/renew', 'NewRentOrderController@renew');
    $router->post('/newRentOrder/buyout', 'NewRentOrderController@buyout');
    $router->post('/newRentOrder/repair', 'NewRentOrderController@repair');

});

/**
 * 新订单支付回调，免登录
 */
$router->group(['prefix' => '/front/trade', 'namespace' => 'Manage'], function () use ($router) {
    $router->post('/callback/payNotify', 'NewOrderPayController@payCallback');
});

/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/trade', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    //订单管理
    $router->post('/order/add', 'OrderController@add');     //添加
    $router->get('/order/list', 'OrderController@list');     //订单列表
    $router->get('/order/detail', 'OrderController@detail'); //订单详情
    $router->get('/order/getOrderNum', 'OrderController@getOrderNum'); //订单数量
    $router->post('/order/cancel', 'OrderController@cancel');     //取消
    $router->get('/orderLogistics/trace', 'LogisticsController@trace'); //订单物流
    $router->post('/order/receive', 'OrderController@receive'); //订单物流
    $router->get('/order/listInvoice', 'OrderController@listInvoice'); //订单发票
    $router->post('/order/addOrderInvoice', 'OrderController@addOrderInvoice'); //订单申请开票

    //购物车相关
    $router->post('/cart/add', 'CartController@add');
    $router->get('/cart/list', 'CartController@list');
    $router->post('/cart/sel', 'CartController@sel');
    $router->post('/cart/editQuantity', 'CartController@editQuantity');
    $router->post('/cart/remove', 'CartController@remove');
    $router->get('/cart/checkout', 'CartController@checkout');
    $router->post('/cart/removeBatch', 'CartController@removeBatch');

    //订单评价相关
    $router->get('/order/storeEvaluationWithContent', 'OrderController@storeEvaluationWithContent');
    $router->post('/order/addOrderComment', 'OrderController@addOrderComment');

    //订单退款相关
    $router->get('/orderReturn/returnItem', 'ReturnController@returnItem');
    $router->post('/orderReturn/add', 'ReturnController@add');
    $router->get('/orderReturn/list', 'ReturnController@list');
    $router->get('/orderReturn/get', 'ReturnController@get');
    $router->post('/orderReturn/cancel', 'ReturnController@cancel');
    $router->post('/orderReturn/edit', 'ReturnController@edit');

    //订单佣金
    $router->get('/distribution/listsOrder', 'DistributionController@listsOrder');


});
