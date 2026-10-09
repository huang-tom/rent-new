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
$router->group(['prefix' => 'manage/cms', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {

    //文章分类
    $router->get('/articleCategory/tree', 'ArticleCategoryController@tree');      //列表
    $router->post('/articleCategory/add', 'ArticleCategoryController@add');       //新增
    $router->post('/articleCategory/edit', 'ArticleCategoryController@edit');     //修改
    $router->post('/articleCategory/remove', 'ArticleCategoryController@remove'); //删除
    $router->post('/articleCategory/removeBatch', 'ArticleCategoryController@removeBatch'); // [新增 2026-09-23] 批量删除，原先 404


    //文章
    $router->get('/articleBase/list', 'ArticleBaseController@list');      //列表
    $router->post('/articleBase/add', 'ArticleBaseController@add');       //新增
    $router->post('/articleBase/edit', 'ArticleBaseController@edit');     //修改
    $router->post('/articleBase/editState', 'ArticleBaseController@editState'); //修改
    $router->post('/articleBase/remove', 'ArticleBaseController@remove'); //删除
    $router->post('/articleBase/removeBatch', 'ArticleBaseController@removeBatch'); //批量删除

    //文章标签 Tag
    $router->get('/articleTag/list', 'ArticleTagController@list');      //列表
    $router->post('/articleTag/add', 'ArticleTagController@add');       //新增
    $router->post('/articleTag/edit', 'ArticleTagController@edit');     //修改
    $router->post('/articleTag/remove', 'ArticleTagController@remove'); //删除
    $router->post('/articleTag/removeBatch', 'ArticleTagController@removeBatch'); //批量删除

    //文章评论 Tag
    $router->get('/articleComment/list', 'ArticleCommentController@list');      //列表
    $router->post('/articleComment/add', 'ArticleCommentController@add');       //新增
    $router->post('/articleComment/edit', 'ArticleCommentController@edit');     //修改
    $router->post('/articleComment/editState', 'ArticleCommentController@editState'); //修改
    $router->post('/articleComment/remove', 'ArticleCommentController@remove'); //删除
    $router->post('/articleComment/removeBatch', 'ArticleCommentController@removeBatch'); //批量删除

});

/**
 * Front 请求路径
 */
$router->group(['prefix' => 'front/cms', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/articleBase/listCategory', 'ArticleController@listCategory');  //分类
    $router->get('/articleBase/get', 'ArticleController@get');       //新增
    $router->get('/articleBase/list', 'ArticleController@list');     //修改

});
