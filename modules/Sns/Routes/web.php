<?php
// +----------------------------------------------------------------------
// | [本地自研模块 2026-09-22] 社交圈子（Sns）路由
// +----------------------------------------------------------------------
// | 接口路径严格对齐前端 src/config/url.config.js 中已存在的 sns 段契约，
// | 使前端无需改动即可调用。
// +----------------------------------------------------------------------

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'manage/sns', 'namespace' => 'Manage', 'middleware' => ['auth', 'admin']], function () use ($router) {

    // 圈子动态（sns_story_base）
    $router->get('/storyBase/list', 'StoryBaseController@list');
    $router->post('/storyBase/editState', 'StoryBaseController@editState'); // 审核 / 置顶 / 隐藏
    $router->post('/storyBase/remove', 'StoryBaseController@remove');

    // 圈子分类（sns_story_category）
    $router->get('/storyCategory/list', 'StoryCategoryController@list');
    $router->post('/storyCategory/add', 'StoryCategoryController@add');
    $router->post('/storyCategory/edit', 'StoryCategoryController@edit');
    $router->post('/storyCategory/remove', 'StoryCategoryController@remove');
    $router->post('/storyCategory/editState', 'StoryCategoryController@editState');

    // 动态评论（sns_story_comment）
    $router->get('/storyComment/list', 'StoryCommentController@list');
    $router->post('/storyComment/editState', 'StoryCommentController@editState');
    $router->post('/storyComment/remove', 'StoryCommentController@remove');
});
