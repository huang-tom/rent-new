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

// [安全修复 v2 · 2026-09-22] 三条厂商开发脚手架接口已彻底下线。
// 背景：上一轮只给本组加了 auth，实测发现「已注册买家」同样能通过 auth（买家/管理员共用 api guard），
//       且 CreateController::info() 仍是字符串拼接 SQL（$table_name 未绑定）→ 注入面依然开放。
//       经全量检索 deploy/admin/src 确认前端 0 处引用 'sys/create'，属纯脚手架，故直接注释。
//       如需恢复，请先把 CreateController 改为管理员专属中间件 + 参数绑定。
$router->group(['prefix' => 'front/sys', 'middleware' => 'auth'], function () use ($router) {
    // $router->get('/create/tables', 'CreateController@tables'); // 枚举全库表名
    // $router->get('/create/info', 'CreateController@info');     // 读表结构（原 SQL 注入点）
    // $router->post('/create/code', 'CreateController@code');    // 触发 Artisan 写文件
    $router->post('/upload/index', 'UploadController@index'); //上传图片 [安全修复] 由公开组移入鉴权组
});


/**
 * Front 请求路径 No Auth
 */
$router->group(['prefix' => 'front/sys', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/page/getMobilePage', 'PageController@getMobilePage'); //首页装修数据
    $router->get('/page/pcLayout', 'PageController@pcLayout'); //PC首页导航数据
    $router->get('/page/getPcPage', 'PageController@getPcPage'); //P首页装修数据

    $router->get('/config/publicKey', 'ConfigController@publicKey');
    $router->get('/config/info', 'ConfigController@info');    //商城配置信息
    $router->get('/config/getPcHelp', 'ConfigController@getPcHelp'); //后端获取PC帮助导航
    $router->get('/config/listTranslateLang', 'ConfigController@listTranslateLang'); //多语言配置信息

    $router->get('/district/tree', 'DistrictController@tree'); //地区表
    $router->get('/express/list', 'ExpressController@list'); //物流公司列表
    $router->get('/feedback/getCategory', 'FeedbackController@getCategory'); //平台反馈类型类别

    $router->get('/captcha/mobile', 'CaptchaController@sendMobileVerifyCode'); //手机验证码
    $router->get('/captcha/index', 'CaptchaController@index'); //图形验证码

});

/**
 * Front 请求路径 Auth
 */
$router->group(['prefix' => 'front/sys', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/feedback/list', 'FeedbackController@list'); //反馈列表
    $router->post('/feedback/add', 'FeedbackController@add'); //添加反馈

});

/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/sys', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //站点设置
    $router->get('/config/index', 'ConfigBaseController@index');
    $router->get('/config/list', 'ConfigBaseController@list');
    $router->post('/config/add', 'ConfigBaseController@add');
    $router->post('/config/edit', 'ConfigBaseController@edit');
    $router->post('/config/remove', 'ConfigBaseController@remove');
    $router->post('/config/editSite', 'ConfigBaseController@editSite');
    $router->post('/config/editState', 'ConfigBaseController@editState');
    $router->post('/config/savePcHelp', 'ConfigBaseController@savePcHelp'); //帮助导航
    $router->get('/config/getDetail', 'ConfigBaseController@getDetail'); //推广设置
    $router->get('/config/smsRecord', 'ConfigBaseController@smsRecord'); // [新增 2026-09-22] 短信记录，原先点一次 404 一次

    //配置类型
    $router->get('/config/listType', 'ConfigTypeController@list');
    $router->post('/config/addType', 'ConfigTypeController@add');
    $router->post('/config/editType', 'ConfigTypeController@edit');
    $router->post('/config/removeType', 'ConfigTypeController@remove');


    //PC装修页面管理
    $router->get('/pageBase/list', 'PageBaseController@list');
    $router->post('/pageBase/add', 'PageBaseController@add');
    $router->post('/pageBase/edit', 'PageBaseController@edit');
    $router->post('/pageBase/remove', 'PageBaseController@remove');
    $router->post('/pageBase/editState', 'PageBaseController@editState');

    //PC装修楼层管理
    $router->get('/pageModule/listTpl', 'PageModuleController@listTpl');
    $router->get('/pageModule/list', 'PageModuleController@list');
    $router->post('/pageModule/add', 'PageModuleController@add');
    $router->post('/pageModule/edit', 'PageModuleController@edit');
    $router->post('/pageModule/remove', 'PageModuleController@remove');
    $router->post('/pageModule/enable', 'PageModuleController@enable');
    $router->post('/pageModule/sort', 'PageModuleController@sort');

    //WAP装修页面管理
    $router->get('/pageBase/listMobile', 'PageBaseController@listMobile');
    $router->post('/pageBase/saveMobile', 'PageBaseController@saveMobile');
    $router->get('/pageBase/getDataInfo', 'PageBaseController@getDataInfo');


    //PC页面导航
    $router->get('/pagePcNav/list', 'PagePcNavController@list');
    $router->post('/pagePcNav/add', 'PagePcNavController@add');
    $router->post('/pagePcNav/edit', 'PagePcNavController@edit');
    $router->post('/pagePcNav/remove', 'PagePcNavController@remove');
    $router->post('/pagePcNav/editState', 'PagePcNavController@editState');

    //分类导航
    $router->get('/pageCategoryNav/list', 'PageCategoryNavController@list');
    $router->post('/pageCategoryNav/add', 'PageCategoryNavController@add');
    $router->post('/pageCategoryNav/edit', 'PageCategoryNavController@edit');
    $router->post('/pageCategoryNav/remove', 'PageCategoryNavController@remove');
    $router->post('/pageCategoryNav/editState', 'PageCategoryNavController@editState');

    //地区管理
    $router->get('/districtBase/list', 'DistrictBaseController@list');
    $router->post('/districtBase/add', 'DistrictBaseController@add');
    $router->post('/districtBase/edit', 'DistrictBaseController@edit');
    $router->post('/districtBase/remove', 'DistrictBaseController@remove');
    $router->post('/districtBase/removeBatch', 'DistrictBaseController@removeBatch'); // [新增 2026-09-22] 前端一直在调，原先 404
    $router->get('/districtBase/tree', 'DistrictBaseController@tree');

    //素材管理
    $router->get('/material/list', 'MaterialBaseController@list');
    $router->post('/material/add', 'MaterialBaseController@add');                 // [新增 2026-09-22] 前端 doAdd 一直指向，原先未注册
    $router->post('/material/edit', 'MaterialBaseController@edit');
    $router->post('/material/remove', 'MaterialBaseController@remove');
    $router->post('/material/removeBatch', 'MaterialBaseController@removeBatch'); // [新增 2026-09-22] 「批量删除」按钮是活的，原先点一次 404 一次

    //素材分类管理
    $router->get('/material/listGallery', 'MaterialGalleryController@list');
    $router->post('/material/addGallery', 'MaterialGalleryController@add');
    $router->post('/material/editGallery', 'MaterialGalleryController@edit');
    $router->post('/material/removeGallery', 'MaterialGalleryController@remove');

    //快递公司
    $router->get('/expressBase/list', 'ExpressBaseController@list');
    $router->post('/expressBase/add', 'ExpressBaseController@add');
    $router->post('/expressBase/edit', 'ExpressBaseController@edit');
    $router->post('/expressBase/remove', 'ExpressBaseController@remove');
    $router->post('/expressBase/removeBatch', 'ExpressBaseController@removeBatch'); // [新增 2026-09-22] 前端一直在调，原先 404
    $router->get('/expressBase/getExpressList', 'ExpressBaseController@enableList');
    $router->post('/expressBase/editState', 'ExpressBaseController@editState');

    //清理缓存
    $router->post('/cache/clean', 'CacheController@clean'); // [新增 2026-09-22] 「清理缓存」按钮原先 404

    //消息模板
    $router->get('/messageTemplate/list', 'MessageTemplateController@list');
    $router->post('/messageTemplate/add', 'MessageTemplateController@add');
    $router->post('/messageTemplate/edit', 'MessageTemplateController@edit');
    $router->post('/messageTemplate/remove', 'MessageTemplateController@remove');
    $router->post('/messageTemplate/editEnable', 'MessageTemplateController@editState');

    //反馈类型
    $router->get('/feedbackType/list', 'FeedbackTypeController@list');
    $router->post('/feedbackType/add', 'FeedbackTypeController@add');
    $router->post('/feedbackType/edit', 'FeedbackTypeController@edit');
    $router->post('/feedbackType/remove', 'FeedbackTypeController@remove');
    $router->post('/feedbackType/editState', 'FeedbackTypeController@editState');

    //反馈分类
    $router->get('/feedbackCategory/list', 'FeedbackCategoryController@list');
    $router->post('/feedbackCategory/add', 'FeedbackCategoryController@add');
    $router->post('/feedbackCategory/edit', 'FeedbackCategoryController@edit');
    $router->post('/feedbackCategory/remove', 'FeedbackCategoryController@remove');
    $router->post('/feedbackCategory/editState', 'FeedbackCategoryController@editState');

    //反馈列表
    $router->get('/feedbackBase/list', 'FeedbackBaseController@list');
    $router->post('/feedbackBase/add', 'FeedbackBaseController@add');     // [新增 2026-09-22] 前端 doAdd 一直指向，原先未注册
    $router->post('/feedbackBase/edit', 'FeedbackBaseController@edit');   // [新增 2026-09-22] 前端 doEdit 一直指向，原先未注册（回复请走 editAnswer）
    $router->post('/feedbackBase/editAnswer', 'FeedbackBaseController@editAnswer');
    $router->post('/feedbackBase/remove', 'FeedbackBaseController@remove');

    //保障服务
    $router->get('/contractType/list', 'ContractTypeController@list');
    $router->post('/contractType/add', 'ContractTypeController@add');
    $router->post('/contractType/edit', 'ContractTypeController@edit');
    $router->post('/contractType/remove', 'ContractTypeController@remove');
    $router->post('/contractType/editState', 'ContractTypeController@editState');

    //字典分类
    $router->get('/dict/list', 'DictBaseController@list');
    $router->post('/dict/add', 'DictBaseController@add');
    $router->post('/dict/edit', 'DictBaseController@edit');
    $router->post('/dict/remove', 'DictBaseController@remove');

    //字典项
    $router->get('/dict/listItem', 'DictItemController@list');
    $router->post('/dict/addItem', 'DictItemController@add');
    $router->post('/dict/editItem', 'DictItemController@edit');
    $router->post('/dict/removeItem', 'DictItemController@remove');

    //计划任务
    $router->get('/crontabBase/list', 'CrontabBaseController@list');
    $router->post('/crontabBase/add', 'CrontabBaseController@add');       // [新增 2026-09-22] 前端 doAdd 一直指向，原先未注册
    $router->post('/crontabBase/edit', 'CrontabBaseController@edit');
    $router->post('/crontabBase/remove', 'CrontabBaseController@remove'); // [新增 2026-09-22] 前端 doRemove 一直指向，原先未注册
    $router->post('/crontabBase/editState', 'CrontabBaseController@editState');


    $router->get('/logAction/list', 'LogActionController@list'); //操作日志
    $router->get('/logError/list', 'LogErrorController@list');   //错误日志

    //国际化
    $router->get('/currencyBase/list', 'CurrencyBaseController@list');
    $router->post('/currencyBase/edit', 'CurrencyBaseController@edit');
    $router->post('/currencyBase/editState', 'CurrencyBaseController@editState');
    $router->post('/currencyBase/remove', 'CurrencyBaseController@remove');
    $router->post('/currencyBase/add', 'CurrencyBaseController@add');

    //语言包
    $router->get('/langStandard/list', 'LangStandardController@list');
    $router->post('/langStandard/add', 'LangStandardController@add');
    $router->post('/langStandard/edit', 'LangStandardController@edit');
    $router->post('/langStandard/remove', 'LangStandardController@remove');

    //内容语言
    $router->get('/langMeta/list', 'LangMetaController@list');

});


