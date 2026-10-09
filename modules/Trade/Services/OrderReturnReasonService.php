<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\OrderReturnReasonRepository;
use Modules\Trade\Repositories\Models\OrderReturnReason;

/**
 * Class OrderReturnReasonService.
 *
 * @package Modules\Trade\Services
 */
class OrderReturnReasonService extends BaseService
{

    public function __construct(OrderReturnReasonRepository $orderReturnReasonRepository)
    {
        $this->repository = $orderReturnReasonRepository;
    }


    /**
     * 删除退款原因（支持单个 / 批量）
     *
     * [重写 2026-09-22] 覆盖 BaseService::remove()。
     *
     * 背景：OrderReturnReasonController::remove() 早就写好了，只是 Trade/Routes/web.php
     *      里从没注册过这条路由（同文件里 list/add/edit 都在）→ 点一次 404 一次。
     *      补路由的同时，把删除做成入参三种形态（单值 / 逗号串 / 数组）通吃：
     *      前端批量删除统一是 selectRows.map(i => i.id).join()，
     *      而 BaseService::remove() 最终会落到被混淆的 BaseRepository::remove()，
     *      它对逗号串的行为无法从源码确认（4 个 core 基类被混淆，见排查报告）。
     *
     * @param $return_reason_id 单个 ID / 逗号拼接串 / 数组
     * @return bool
     * @throws ErrorException
     */
    public function remove($return_reason_id)
    {
        $ids = is_array($return_reason_id) ? $return_reason_id : explode(',', (string)$return_reason_id);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $deleted = OrderReturnReason::whereIn('return_reason_id', $ids)->delete();
        if ($deleted > 0) {
            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}
