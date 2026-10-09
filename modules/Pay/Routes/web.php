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

$router->group(['prefix' => 'manage/pay', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    $router->get('/consumeRecord/list', 'ConsumeRecordController@list'); //收支明细
    $router->get('/consumeTrade/list', 'ConsumeTradeController@list'); //交易订单

    $router->get('/consumeDeposit/list', 'ConsumeDepositController@list'); //充值记录
    $router->post('/consumeDeposit/offline', 'ConsumeDepositController@offlinePay'); //添加收款记录
    //[新增 2026-09-23] 补 /consumeDeposit/editReview。
    //  充值记录页「收款确认」列里的 el-switch（@change="handleState" → editReview）
    //  是活的：既没有 v-if="false" 也没有 v-permissions 门槛，admin 一定看得到、点得动。
    //  此前 404，点一下就「操作失败」。全页只有这一个开关是活的，
    //  添加/编辑/删除按钮都是被 HTML 注释掉的（死声明，不在本次范围）。
    $router->post('/consumeDeposit/editReview', 'ConsumeDepositController@editReview'); //收款确认

    $router->get('/userResource/list', 'UserResourceController@list'); //支付会员
    $router->post('/userResource/updateUserMoney', 'UserResourceController@updateUserMoney'); //修改余额
    $router->post('/userResource/updatePoints', 'UserResourceController@updatePoints'); //修改积分

    $router->get('/userPointsHistory/list', 'UserPointsHistoryController@list'); //积分明细

    $router->get('/baseBank/list', 'BaseBankController@list'); //银行管理列表
    $router->post('/baseBank/edit', 'BaseBankController@edit'); //银行管理
    $router->post('/baseBank/remove', 'BaseBankController@remove'); //银行管理
    $router->post('/baseBank/add', 'BaseBankController@add'); //银行管理


    //提现列表
    $router->get('/consumeWithdraw/list', 'ConsumeWithdrawController@list');
    $router->post('/consumeWithdraw/add', 'ConsumeWithdrawController@add');
    $router->post('/consumeWithdraw/edit', 'ConsumeWithdrawController@edit');
    $router->post('/consumeWithdraw/remove', 'ConsumeWithdrawController@remove');

});


/**
 * Front 请求路径 No Auth
 */
$router->group(['prefix' => '/front/pay', 'namespace' => 'Front'], function () use ($router) {

    $router->post('/consumeDeposit/alipayPay', 'AlipayController@pay'); //支付宝支付
    $router->post('/consumeDeposit/alipayPcPay', 'AlipayController@pcPay'); //支付宝支付

    $router->get('/callback/alipayReturn', 'AlipayController@alipayReturn'); //支付宝同步回调
    $router->post('/callback/alipayNotify', 'AlipayController@alipayNotify'); //支付宝异步回调

    $router->post('/consumeDeposit/wechatNativePay', 'WechatController@wechatNativePay'); //微信支付-PC
    $router->post('/consumeDeposit/wechatH5Pay', 'WechatController@h5Pay'); //微信支付-H5

    $router->post('/callback/wechatNotify', 'WechatController@wechatNotify'); //微信支付回调
    $router->get('/callback/wechatCheck', 'WechatController@wechatCheck'); //微信支付状态判断
});


/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/pay', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/userResource/signState', 'ResourceController@signState');
    $router->post('/userResource/signIn', 'ResourceController@signIn');
    $router->get('/userResource/getSignInfo', 'ResourceController@getSignInfo');
    $router->get('/points/list', 'PointsController@list');


    $router->post('/consumeDeposit/moneyPay', 'PaymentIndexController@moneyPay'); //余额支付

    $router->get('/consumeRecord/list', 'ConsumeController@list');

    //提现账户管理
    $router->get('/userBank/list', 'UserBankController@list');
    $router->get('/userBank/get', 'UserBankController@get');
    $router->post('/userBank/addOrEditUserBank', 'UserBankController@addOrEditUserBank');
    $router->post('/userBank/remove', 'UserBankController@remove');

    $router->get('/index/getPayPasswd', 'IndexController@getPayPasswd'); //用户支付密码
    $router->post('/index/changePayPassword', 'IndexController@changePayPassword'); //设置支付密码

    $router->get('/userResource/listsExp', 'ResourceController@listsExp'); //经验值列表
});
