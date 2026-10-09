<?php

namespace Modules\Admin\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class AdminRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     * admin 表相关实例
     */
    public function register()
    {
        //管理菜单
        $this->app->bind(\Modules\Admin\Repositories\Contracts\MenuBaseRepository::class,
            \Modules\Admin\Repositories\Eloquent\MenuBaseRepositoryEloquent::class);

        //管理员
        $this->app->bind(\Modules\Admin\Repositories\Contracts\UserAdminRepository::class,
            \Modules\Admin\Repositories\Eloquent\UserAdminRepositoryEloquent::class);

        //角色菜单
        $this->app->bind(\Modules\Admin\Repositories\Contracts\UserRoleRepository::class,
            \Modules\Admin\Repositories\Eloquent\UserRoleRepositoryEloquent::class);
    }
}
