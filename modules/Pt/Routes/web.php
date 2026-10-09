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
$router->group(['prefix' => 'front/pt', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/product/listCategory', 'ProductController@listCategory'); //商品分类列表
    $router->get('/product/treeCategory', 'ProductController@treeCategory'); //商品树形列表
    $router->get('/product/list', 'ProductController@list');     //商品列表
    $router->get('/product/detail', 'ProductController@detail'); //商品详情
    $router->get('/product/listAllCategory', 'ProductController@listAllCategory'); //商品全部分类
    $router->get('/product/brand', 'ProductController@brand'); //推荐品牌
    $router->get('/product/getComment', 'ProductController@getComment'); //商品评论

    $router->get('/product/listItem', 'ProductController@listItem');
    $router->get('/product/getSearchFilter', 'ProductController@getSearchFilter'); //商品筛选属性

});

$router->group(['prefix' => 'manage/pt', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    $router->get('/productCategory/tree', 'ProductCategoryController@tree');
    $router->get('/productCategory/list', 'ProductCategoryController@list');
    $router->post('/productCategory/add', 'ProductCategoryController@add');
    $router->post('/productCategory/edit', 'ProductCategoryController@edit');
    $router->post('/productCategory/remove', 'ProductCategoryController@remove');
    $router->post('/productCategory/editState', 'ProductCategoryController@editState');

    //商品类型
    $router->get('/productType/list', 'ProductTypeController@list');
    $router->get('/productType/info', 'ProductTypeController@info');
    $router->post('/productType/add', 'ProductTypeController@add');
    $router->post('/productType/edit', 'ProductTypeController@edit');
    $router->post('/productType/remove', 'ProductTypeController@remove');

    //商品属性
    $router->get('/productAssist/list', 'ProductAssistController@list');
    $router->post('/productAssist/add', 'ProductAssistController@add');
    $router->post('/productAssist/edit', 'ProductAssistController@edit');
    $router->post('/productAssist/remove', 'ProductAssistController@remove');
    $router->get('/productAssist/tree', 'ProductAssistController@tree');
    $router->get('/productAssistItem/list', 'ProductAssistItemController@list');
    $router->post('/productAssistItem/add', 'ProductAssistItemController@add');
    $router->post('/productAssistItem/edit', 'ProductAssistItemController@edit');
    $router->post('/productAssistItem/remove', 'ProductAssistItemController@remove');

    //商品品牌
    $router->get('/productBrand/list', 'ProductBrandController@list');
    $router->get('productBrand/tree', 'ProductBrandController@tree');
    $router->post('/productBrand/add', 'ProductBrandController@add');
    $router->post('/productBrand/edit', 'ProductBrandController@edit');
    $router->post('/productBrand/remove', 'ProductBrandController@remove');
    $router->post('/productBrand/editState', 'ProductBrandController@editState');

    //商品规格
    $router->get('/productSpec/list', 'ProductSpecController@list');
    $router->post('/productSpec/add', 'ProductSpecController@add');
    $router->post('/productSpec/edit', 'ProductSpecController@edit');
    $router->post('/productSpec/remove', 'ProductSpecController@remove');
    $router->get('/productSpec/tree', 'ProductSpecController@tree');
    $router->get('/productSpecItem/list', 'ProductSpecItemController@list');
    $router->post('/productSpecItem/add', 'ProductSpecItemController@add');
    $router->post('/productSpecItem/edit', 'ProductSpecItemController@edit');
    $router->post('/productSpecItem/remove', 'ProductSpecItemController@remove');
    $router->post('/productSpecItem/editState', 'ProductSpecItemController@editState');

    //商品标签
    $router->get('/productTag/list', 'ProductTagController@list');
    $router->post('/productTag/add', 'ProductTagController@add');
    $router->post('/productTag/edit', 'ProductTagController@edit');
    $router->post('/productTag/remove', 'ProductTagController@remove');

    //商品咨询（问答）
    //[新增 2026-09-23] 这 5 个接口此前**完全缺失**（连路由都没有，全部 404）。
    //  但后台页面 views/pt/productAskBase/index.vue 与 4 条权限行
    //  （menu_id 4287/4289/4290/4291）都已随包下发，且 4 个角色全部已授权，
    //  也就是说菜单「商品管理 → 商品问答」是**能打开的**、按钮也是**渲染出来的**，
    //  只是一点就 404。属于必须补的真实缺陷，不是死声明。
    $router->get('/productAskBase/list', 'ProductAskBaseController@list');
    $router->post('/productAskBase/add', 'ProductAskBaseController@add');
    $router->post('/productAskBase/edit', 'ProductAskBaseController@edit');
    $router->post('/productAskBase/remove', 'ProductAskBaseController@remove');
    $router->post('/productAskBase/removeBatch', 'ProductAskBaseController@removeBatch');

    //商品评论
    $router->get('/productComment/list', 'ProductCommentController@list');
    $router->post('/productComment/add', 'ProductCommentController@add');
    $router->post('/productComment/edit', 'ProductCommentController@edit');
    $router->post('/productComment/remove', 'ProductCommentController@remove');
    $router->post('/productComment/editState', 'ProductCommentController@editState');
    //评论回复
    $router->get('/productCommentReply/list', 'ProductCommentReplyController@list');
    $router->post('/productCommentReply/editState', 'ProductCommentReplyController@editState');
    $router->post('/productCommentReply/add', 'ProductCommentReplyController@add');


    //商品列表
    $router->get('/productBase/list', 'ProductBaseController@list');
    $router->post('/productBase/save', 'ProductBaseController@save');
    $router->post('/productBase/remove', 'ProductBaseController@remove');
    $router->post('/productBase/editState', 'ProductBaseController@editState');
    $router->get('/productBase/getProductDate', 'ProductBaseController@getProduct');
    $router->get('/productBase/listItem', 'ProductBaseController@listItem');
    $router->post('/productBase/editEnable', 'ProductBaseController@editState');
    $router->post('/productBase/editCommissionRate', 'ProductBaseController@editCommissionRate');
    $router->post('/productBase/editSort', 'ProductBaseController@editSort');
    $router->post('/productBase/batchEditState', 'ProductBaseController@batchEditState');

    //商品SKU
    $router->get('/productItem/list', 'ProductItemController@list');
    $router->post('/productItem/editState', 'ProductItemController@editState');
    $router->post('/productItem/editStock', 'ProductItemController@editStock');
    $router->get('/productItem/getStockBillItems', 'ProductItemController@getStockBillItems');
    $router->get('/productItem/getStockWarningItems', 'ProductItemController@getStockWarningItems');

    //导入模板下载
    //[新增 2026-09-23] 补两个 exportTemp。这两个是**真缺陷**，不是死声明：
    //  「商品列表」页(views/pt/productBase/index.vue)第 11/14 行的两个按钮
    //   （@click="importEdit" / "importEditItem"）是活的，权限 /manage/pt/productBase/edit
    //   也已下发；点开弹窗后「下载模板」直接调这两个接口，此前 404。
    //  这是本次 A/B/C 三类里唯一能靠「前端页面文件名相同」之外的线索找到的断链 ——
    //  productItem 的入口长在 productBase 页面上，只按页面名匹配会漏掉。
    //⚠️ 配套的 productBase/importTemp、productItem/importTemp **后端完全不存在**，
    //  本次不实现：那是「新增导入功能」（需要 xlsx 解析 + 商品多表写入 + 字段校验规则），
    //  属于需求开发而非补断链。
    $router->get('/productBase/exportTemp', 'ProductBaseController@exportTemp');
    $router->get('/productItem/exportTemp', 'ProductItemController@exportTemp');

});
