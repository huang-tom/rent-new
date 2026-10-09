<?php
// +----------------------------------------------------------------------
// | 后台管理员鉴权中间件（本地安全加固）
// +----------------------------------------------------------------------
// | 说明：原系统 manage/* 路由仅挂 auth（校验登录态），买家与管理员共用
// | api guard，导致已注册买家可直接访问后台接口。本中间件在 auth 之后
// | 校验当前用户在 user_admin 表中存在管理员记录，否则拒绝。
// +----------------------------------------------------------------------

namespace App\Middleware;

use App\Support\Respond;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class AdminAuthenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            return Respond::error('尚未登录', 0);
        }

        $user = $this->auth->guard($guard)->user();

        // 买家（user_admin 表无记录）一律拒绝访问 manage/* 后台接口
        if (!$user || !$user->getIsAdminAttribute()) {
            return Respond::error('无权限访问', 0);
        }

        return $next($request);
    }
}
