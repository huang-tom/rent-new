<?php

namespace App\Providers;

use Modules\Account\Repositories\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Admin\Repositories\Models\MenuBase;
use Modules\Admin\Repositories\Models\UserRole;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        // 定义一个 Gate 来判断用户是否有访问某个菜单的权限
        Gate::define('access-menu', function (User $user, $menuPath) {

            if ($user->isSuperAdmin()) {
                //return true;
            }

            // 只有管理员才进行菜单权限校验
            if (!$user->isAdmin()) {
                return false;
            }

            $user_role_id = $user->userRoleId();
            $role = UserRole::find($user_role_id);
            if (!$role) {
                return false; // 如果找不到角色信息，返回拒绝访问
            }

            // 获取所有菜单权限ID
            $permissions = $role->getMenuPermissions();

            // 查找菜单表中与路径对应的菜单ID
            $menu = MenuBase::where('menu_permission', '/' . $menuPath)->first();
            if (!$menu) {
                return false; // 如果找不到菜单，返回拒绝访问
            }

            // 检查用户角色的权限中是否包含该菜单ID
            return in_array($menu->menu_id, $permissions);
        });

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
