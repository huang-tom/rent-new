<?php

namespace Modules\Shop\Services;

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Shop\Repositories\Contracts\StoreExpressLogisticsRepository;
use Modules\Trade\Repositories\Models\OrderLogistics;
use App\Exceptions\ErrorException;

/**
 * Class StoreExpressLogisticsService.
 *
 * @package Modules\Shop\Services
 */
class StoreExpressLogisticsService extends BaseService
{
    public function __construct(StoreExpressLogisticsRepository $storeExpressLogisticsRepository)
    {
        $this->repository = $storeExpressLogisticsRepository;
    }

    public function addExpressLogistics($request)
    {
        DB::beginTransaction();

        try {

            $logistics_is_default = $request['logistics_is_default'];
            if ($logistics_is_default) {
                $this->repository->editWhere(['logistics_is_default' => true], ['logistics_is_default' => false]);
            }

            $this->repository->add($request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }

    public function editExpressLogistics($logistics_id, $request)
    {
        DB::beginTransaction();

        try {

            $logistics_is_default = $request['logistics_is_default'];
            if ($logistics_is_default) {
                $this->repository->editWhere(['logistics_is_default' => true], ['logistics_is_default' => false]);
            }

            $this->repository->edit($logistics_id, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function editState($request)
    {
        DB::beginTransaction();
        $logistics_id = $request->get('logistics_id');

        try {

            $state_data = [];

            if ($request->has('logistics_is_default')) {
                $state_data['logistics_is_default'] = $request->boolean('logistics_is_default');
                if ($state_data['logistics_is_default']) {
                    $this->repository->editWhere(['logistics_is_default' => true], ['logistics_is_default' => false]);
                }
            }

            if ($request->has('logistics_is_enable')) {
                $state_data['logistics_is_enable'] = $request->boolean('logistics_is_enable');
            }

            $this->repository->edit($logistics_id, $state_data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 批量删除物流公司（发货信息）
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeExpressLogistics/removeBatch`（原先 404）。
     * 前端 views/shop/storeExpressLogistics/index.vue 的批量删除传**逗号串** `{logistics_id: "1,2"}`。
     *
     * 为什么要挡一道"被订单引用"的校验：
     *   trade_order_logistics.logistics_id 存的就是这里的 logistics_id。
     *   发货信息被删后，历史订单的发货记录会指向不存在的物流公司，
     *   订单详情里发货人/联系电话整块变空 —— 这是历史数据不可逆的损坏，
     *   所以和快递公司（ExpressBase）那边"有物流在用则不可删"保持同一策略。
     *
     * @param $logistics_ids 逗号串或数组
     * @return bool
     * @throws ErrorException
     */
    public function removeBatch($logistics_ids)
    {
        $ids = is_array($logistics_ids) ? $logistics_ids : explode(',', (string)$logistics_ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $used = OrderLogistics::whereIn('logistics_id', $ids)->count();
        if ($used > 0) {
            throw new ErrorException(sprintf(__('选中的物流公司中有 %d 条订单发货记录在使用，不可删除'), $used));
        }

        foreach ($ids as $logistics_id) {
            $this->repository->remove($logistics_id);
        }

        return true;
    }

}
