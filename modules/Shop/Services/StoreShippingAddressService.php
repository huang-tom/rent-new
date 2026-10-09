<?php

namespace Modules\Shop\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Shop\Repositories\Contracts\StoreShippingAddressRepository;
use Modules\Trade\Repositories\Models\OrderLogistics;

/**
 * Class StoreShippingAddressService.
 *
 * @package Modules\Shop\Services
 */
class StoreShippingAddressService extends BaseService
{
    public function __construct(StoreShippingAddressRepository $storeShippingAddressRepository)
    {
        $this->repository = $storeShippingAddressRepository;
    }

    public function addShippingAddress($request)
    {
        DB::beginTransaction();

        try {

            $ss_is_default = $request['ss_is_default'];
            if ($ss_is_default) {
                $this->repository->editWhere(['ss_is_default' => true], ['ss_is_default' => false]);
            }

            $this->repository->add($request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }

    public function editShippingAddress($ss_id, $request)
    {
        DB::beginTransaction();

        try {

            $ss_is_default = $request['ss_is_default'];
            if ($ss_is_default) {
                $this->repository->editWhere(['ss_is_default' => true], ['ss_is_default' => false]);
            }

            $this->repository->edit($ss_id, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 批量删除发货地址
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeShippingAddress/removeBatch`（原先 404）。
     * 前端 views/shop/storeShippingAddress/index.vue 批量删除传**逗号串** `{ss_id: "1,2"}`。
     *
     * 与物流公司同理，发货地址也被 trade_order_logistics.ss_id 引用：
     * 删掉正在被订单使用的地址，会让历史订单的发货地址字段整块变空。
     *
     * @param $ss_ids 逗号串或数组
     * @return bool
     * @throws ErrorException
     */
    public function removeBatch($ss_ids)
    {
        $ids = is_array($ss_ids) ? $ss_ids : explode(',', (string)$ss_ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $used = OrderLogistics::whereIn('ss_id', $ids)->count();
        if ($used > 0) {
            throw new ErrorException(sprintf(__('选中的发货地址中有 %d 条订单发货记录在使用，不可删除'), $used));
        }

        foreach ($ids as $ss_id) {
            $this->repository->remove($ss_id);
        }

        return true;
    }

}
