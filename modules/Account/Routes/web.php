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

$router->group(['prefix' => 'front/account', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/login/logout', 'AuthController@logout');  //用户退出
    $router->get('/user/info', 'Front\UserController@info');       //获取用户信息
    $router->get('/userMessage/getNotice', 'Front\MessageController@getNotice'); //获取用户信息

});

$router->post('/front/account/login/login', 'LoginController@login');    //用户登录-账号密码登录
$router->get('/front/account/login/doSmsLogin', 'LoginController@doSmsLogin');    //用户登录-手机验证码登录
$router->post('/front/account/login/register', 'LoginController@register'); //用户注册
$router->get('/front/account/login/protocol', 'LoginController@protocol'); //协议
$router->post('/front/account/login/setNewPassword', 'LoginController@setNewPassword'); //设置登录密码

/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/account', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //会员管理
    $router->get('/userInfo/list', 'UserInfoController@list');
    $router->get('/userInfo/getUserData', 'UserInfoController@getUserData');
    $router->post('/userInfo/add', 'UserInfoController@add');
    $router->post('/userInfo/edit', 'UserInfoController@edit');
    $router->post('/userInfo/passWordEdit', 'UserInfoController@passWordEdit');
    $router->post('/userInfo/myPassWordEdit', 'UserInfoController@myPassWordEdit'); //修改自己的密码
    $router->post('/userInfo/remove', 'UserInfoController@remove');
    // ↓ [新增 2026-09-23] 以下 5 条前端一直在调、后端从无声明，每次点都是 404
    $router->post('/userInfo/editState', 'UserInfoController@editState');       //认证审核 / 账号启停
    $router->post('/userInfo/addTags', 'UserInfoController@addTags');           //批量设置标签
    $router->post('/userInfo/addVouchers', 'UserInfoController@addVouchers');   //批量发放优惠券
    $router->post('/userInfo/exportFile', 'UserInfoController@exportFile');     //导出会员
    $router->get('/userInfo/exportTemp', 'UserInfoController@exportTemp');      //下载导入模板

    //会员收货地址（管理端：前台建单时替会员选/建地址）
    // [新增 2026-09-23] 原先只有 front/account/userDeliveryAddress/*（会员改自己的地址），
    // 管理端一条都没有 → 建单页地址下拉为空、新增必 404。
    $router->get('/userDeliveryAddress/list', 'DeliveryAddressController@list');
    $router->post('/userDeliveryAddress/add', 'DeliveryAddressController@add');
    $router->post('/userDeliveryAddress/edit', 'DeliveryAddressController@edit');
    $router->post('/userDeliveryAddress/remove', 'DeliveryAddressController@remove');

    //会员发票抬头（管理端：订单开票时选抬头）
    // [新增 2026-09-23] 同上，原先只有 Front 版
    $router->get('/userInvoice/list', 'InvoiceController@list');
    $router->post('/userInvoice/add', 'InvoiceController@add');
    $router->post('/userInvoice/edit', 'InvoiceController@edit');
    $router->post('/userInvoice/remove', 'InvoiceController@remove');

    $router->get('/userBindConnect/list', 'UserBindConnectController@list'); //用户绑定信息

    //会员等级
    $router->get('/userLevel/list', 'UserLevelController@list');
    $router->post('/userLevel/add', 'UserLevelController@add');
    $router->post('/userLevel/edit', 'UserLevelController@edit');
    $router->post('/userLevel/remove', 'UserLevelController@remove');

    //会员标签组管理
    $router->get('/userTagGroup/tree', 'UserTagGroupController@tree');
    $router->get('/userTagGroup/list', 'UserTagGroupController@list');      //列表
    $router->post('/userTagGroup/add', 'UserTagGroupController@add');       //新增
    $router->post('/userTagGroup/edit', 'UserTagGroupController@edit');     //修改
    $router->post('/userTagGroup/remove', 'UserTagGroupController@remove'); //删除

    //会员标签管理
    $router->get('/userTagBase/list', 'UserTagBaseController@list');      //列表
    $router->post('/userTagBase/add', 'UserTagBaseController@add');       //新增
    $router->post('/userTagBase/edit', 'UserTagBaseController@edit');     //修改
    $router->post('/userTagBase/remove', 'UserTagBaseController@remove'); //删除
    $router->post('/userTagBase/editState', 'UserTagBaseController@editState'); //状态变更

    //会员消息管理
    $router->get('/userMessage/list', 'UserMessageController@list');      //列表
    $router->post('/userMessage/add', 'UserMessageController@add');       //新增
    $router->post('/userMessage/edit', 'UserMessageController@edit');     //修改
    $router->post('/userMessage/remove', 'UserMessageController@remove'); //删除
    $router->get('/userMessage/getNotice', 'UserMessageController@getNotice'); //获取通知
    $router->post('/userMessage/editState', 'UserMessageController@editState'); //状态变更

    //推广员
    $router->get('/userDistribution/list', 'UserDistributionController@list');      //列表
    $router->post('/userDistribution/add', 'UserDistributionController@add');       //新增
    $router->post('/userDistribution/edit', 'UserDistributionController@edit');     //修改
    $router->post('/userDistribution/editState', 'UserDistributionController@editState'); // [新增 2026-09-23] 生效/停用，原先 404

});


/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/account', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->post('/user/edit', 'UserController@edit'); //会员信息修改
    $router->post('/user/bindMobile', 'UserController@bindMobile'); //会员绑定手机号
    $router->post('/user/unBindMobile', 'UserController@unBindMobile'); //会员解绑手机号
    $router->post('/user/saveCertificate', 'UserController@saveCertificate'); //实名认证
    $router->get('/user/getCompanyByUserId', 'UserController@getCompanyByUserId');

    //会员地址管理
    $router->get('/userDeliveryAddress/list', 'DeliveryAddressController@list');  //列表
    $router->get('/userDeliveryAddress/get', 'DeliveryAddressController@get');    //详情
    $router->post('/userDeliveryAddress/add', 'DeliveryAddressController@add');   //新增
    $router->post('/userDeliveryAddress/save', 'DeliveryAddressController@save'); //修改
    $router->post('/userDeliveryAddress/remove', 'DeliveryAddressController@remove'); //删除

    //用户发票管理
    $router->get('/userInvoice/list', 'InvoiceController@list');   //列表
    $router->get('/userInvoice/get', 'InvoiceController@get');    //获取
    $router->post('/userInvoice/add', 'InvoiceController@add');    //新增
    $router->post('/userInvoice/edit', 'InvoiceController@edit');  //修改
    $router->post('/userInvoice/remove', 'InvoiceController@remove');  //删除
    $router->get('/userInvoice/getInvoiceTips', 'InvoiceController@getInvoiceTips');

    //用户消息
    $router->get('/userMessage/getMsgCount', 'MessageController@getMsgCount');
    $router->get('/userMessage/list', 'MessageController@list');
    $router->get('/userMessage/get', 'MessageController@get');
    $router->get('/userMessage/getImConfig', 'MessageController@getImConfig');
    $router->get('/userMessage/getMessageNum', 'MessageController@getMessageNum');
    $router->post('/userMessage/add', 'MessageController@add');
    $router->post('/userMessage/setRead', 'MessageController@setRead');


    $router->get('/user/listBaseUserLevel', 'UserController@listBaseUserLevel');   //会员等级列表
    $router->get('/user/listsExpRule', 'UserController@listsExpRule');   //经验值规则

});
