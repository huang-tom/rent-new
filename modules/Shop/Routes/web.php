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
$router->group(['prefix' => 'front/shop', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/mobile/getSearchInfo', 'MobileController@getSearchInfo');

});


/**
 * Front 请求路径 Auth
 */
$router->group(['prefix' => 'front/shop', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/userFavoritesItem/lists', 'FavoritesItemController@list'); //收藏商品列表
    $router->post('/userFavoritesItem/add', 'FavoritesItemController@add'); //收藏商品
    $router->post('/userFavoritesItem/remove', 'FavoritesItemController@remove'); //取消收藏

    $router->get('/userVoucher/list', 'VoucherController@list'); //用户优惠券列表
    $router->get('/userVoucher/getEachVoucherNum', 'VoucherController@getEachVoucherNum'); //优惠券数量列表
    $router->post('/userVoucher/add', 'VoucherController@add'); //领取优惠券

    $router->get('/userProductBrowse/list', 'ProductBrowseController@list'); //浏览记录
    $router->post('/userProductBrowse/removeBrowser', 'ProductBrowseController@removeBrowser'); //删除浏览记录

});

/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/shop', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //物流公司
    $router->get('/storeExpressLogistics/list', 'StoreExpressLogisticsController@list');
    $router->post('/storeExpressLogistics/add', 'StoreExpressLogisticsController@add');
    $router->post('/storeExpressLogistics/edit', 'StoreExpressLogisticsController@edit');
    $router->post('/storeExpressLogistics/remove', 'StoreExpressLogisticsController@remove');
    $router->post('/storeExpressLogistics/editState', 'StoreExpressLogisticsController@editState');
    $router->post('/storeExpressLogistics/removeBatch', 'StoreExpressLogisticsController@removeBatch');       // [新增 2026-09-23] 原先 404
    $router->post('/storeExpressLogistics/returnLogistics', 'StoreExpressLogisticsController@returnLogistics'); // [新增 2026-09-23] 原先 404（售后单物流详情）

    //发货地址
    $router->get('/storeShippingAddress/list', 'StoreShippingAddressController@list');
    $router->post('/storeShippingAddress/add', 'StoreShippingAddressController@add');
    $router->post('/storeShippingAddress/edit', 'StoreShippingAddressController@edit');
    $router->post('/storeShippingAddress/remove', 'StoreShippingAddressController@remove');
    $router->post('/storeShippingAddress/removeBatch', 'StoreShippingAddressController@removeBatch'); // [新增 2026-09-23] 原先 404

    //物流工具
    $router->get('/storeTransportType/list', 'StoreTransportTypeController@list');
    $router->post('/storeTransportType/add', 'StoreTransportTypeController@add');
    $router->post('/storeTransportType/edit', 'StoreTransportTypeController@edit');
    $router->post('/storeTransportType/remove', 'StoreTransportTypeController@remove');
    $router->post('/storeTransportType/removeBatch', 'StoreTransportTypeController@removeBatch'); // [新增 2026-09-23] 原先 404
    // 说明：`/storeTransportType/editState` 不加。前端 index.vue 里确实有 handleState() 会调它，
    // 但**模板里从未绑定过 handleState**（只定义 + return），属死代码；
    // 且 shop_store_transport_type 表没有"启用/停用"列，硬造一个 editState 只会写回原值、
    // 毫无语义。正确解法是删掉前端那段死代码，而不是在后端补一个空接口。

    //物流工具ITEM
    $router->get('/storeTransportItem/list', 'StoreTransportItemController@list');
    $router->post('/storeTransportItem/add', 'StoreTransportItemController@add');
    $router->post('/storeTransportItem/edit', 'StoreTransportItemController@edit');
    $router->post('/storeTransportItem/remove', 'StoreTransportItemController@remove');


    $router->get('/userVoucher/list', 'UserVoucherController@list'); //用户优惠券列表
});
