<?php
// +----------------------------------------------------------------------
// | [本地自研模块 2026-09-22] 社交圈子（Sns）
// +----------------------------------------------------------------------
// | 背景：厂商安装包里存在 sns_story_* 全套数据表、前端 url.config.js 也已定义
// |       /manage/sns/** 接口契约，但后端 PHP 业务代码、菜单、权限均未交付
// |       （实测该路径返回路由级 404）。本模块按既有契约补齐后端能力。
// +----------------------------------------------------------------------

namespace Modules\Sns\Providers;

use Illuminate\Support\ServiceProvider;

class SnsServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->router->group([
            'namespace' => 'Modules\Sns\Http\Controllers',
        ], function ($router) {
            require __DIR__ . '/../Routes/web.php';
        });
    }
}
