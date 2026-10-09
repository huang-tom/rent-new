<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class CacheController.
 *
 * @package Modules\Sys\Http\Controllers\Manage
 */
class CacheController extends BaseController
{
    /**
     * 清理缓存
     *
     * [新增 2026-09-22] 「系统设置 → 清理缓存」页的按钮调 POST /manage/sys/cache/clean，
     * 后端从未注册这条路由 —— 页面能打开，但点一次 404 一次。
     *
     * 安全性说明（为什么这里 flush() 是可接受的）：
     * 本项目 CACHE_DRIVER=redis，且 .env 对 redis 的库号做了明确分工 ——
     *   REDIS_DB=0（默认） / REDIS_CACHE_DB=1（缓存专用） / REDIS_QUEUE_DB=2（队列）
     * cache.default 指向 connection=cache（即 db1），因此 flush() 的范围被限制在 db1：
     *   · 不影响队列（db2）
     *   · 不影响登录态 —— 本系统用无状态 JWT（token 内含 user_salt），不落 redis
     *   · 被清掉的只有业务缓存，与「清理缓存」这个动作的语义一致
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clean(Request $request)
    {
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            return Respond::error(__('缓存清理失败') . '：' . $e->getMessage());
        }

        return Respond::success([], __('缓存清理成功'));
    }
}
