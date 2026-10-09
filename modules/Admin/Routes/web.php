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
$router->group(['prefix' => 'manage/admin', 'namespace' => 'Manage', 'middleware' => ['auth','admin']], function () use ($router) {
    $router->get('/menu/tree', 'MenuBaseController@tree'); //获取菜单
    $router->post('/menu/add', 'MenuBaseController@add');
    $router->post('/menu/edit', 'MenuBaseController@edit');
    $router->post('/menu/editState', 'MenuBaseController@editState');  //更新状态
    $router->post('/menu/remove', 'MenuBaseController@remove');        // [新增 2026-09-23] 原先连路由都没有，点删除必 404

    //角色管理
    $router->get('/userRole/list', 'UserRoleController@list');
    $router->post('/userRole/add', 'UserRoleController@add');
    $router->post('/userRole/edit', 'UserRoleController@edit');
    $router->post('/userRole/remove', 'UserRoleController@remove');

    //管理员管理
    $router->get('/userAdmin/list', 'UserAdminController@list');
    $router->post('/userAdmin/add', 'UserAdminController@add');
    $router->post('/userAdmin/edit', 'UserAdminController@edit');
    $router->post('/userAdmin/remove', 'UserAdminController@remove');
    $router->post('/userAdmin/removeBatch', 'UserAdminController@removeBatch'); // [新增 2026-09-23] 原先 404
});
