<?php

namespace Modules\Shop\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Shop\Repositories\Contracts\StoreTransportItemRepository;
use Modules\Shop\Repositories\Contracts\StoreTransportTypeRepository;
use App\Exceptions\ErrorException;

/**
 * Class StoreTransportTypeService.
 *
 * @package Modules\Shop\Services
 */
class StoreTransportTypeService extends BaseService
{
    private $storeTransportItemRepository;
    private $productBaseRepository;

    public function __construct(
        StoreTransportTypeRepository $storeTransportTypeRepository,
        StoreTransportItemRepository $storeTransportItemRepository,
        ProductBaseRepository        $productBaseRepository
    )
    {
        $this->repository = $storeTransportTypeRepository;
        $this->storeTransportItemRepository = $storeTransportItemRepository;
        $this->productBaseRepository = $productBaseRepository;
    }


    /**
     * 删除
     * @param $transport_type_id
     * @return bool
     * @throws ErrorException
     */
    public function removeType($transport_type_id)
    {
        $rows = $this->productBaseRepository->find(['transport_type_id' => $transport_type_id]);
        if (!empty($rows)) {
            throw new ErrorException(sprintf(__("有 %d 条商品使用，不可删除"), count($rows)));
        }

        $result = $this->repository->remove($transport_type_id);
        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 批量删除运费模板
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeTransportType/removeBatch`（原先 404）。
     * 前端 views/shop/storeTransportType/index.vue:190 批量删除传**逗号串** `{transport_type_id: "1,2"}`。
     *
     * 逐个复用 removeType()，而不是自己写 whereIn 删除：
     *   removeType 里有「有商品在用则不可删」的校验，商品挂在已删模板上会导致
     *   这些商品运费计算直接抛"商品运费设置有误"、无法下单。
     *   先整体校验再删，避免删掉前两个、第三个才报错留下半个状态。
     *
     * @param $transport_type_ids 逗号串或数组
     * @return bool
     * @throws ErrorException
     */
    public function removeBatchType($transport_type_ids)
    {
        $ids = is_array($transport_type_ids) ? $transport_type_ids : explode(',', (string)$transport_type_ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        // 先整体校验
        foreach ($ids as $transport_type_id) {
            $rows = $this->productBaseRepository->find(['transport_type_id' => $transport_type_id]);
            if (!empty($rows)) {
                throw new ErrorException(sprintf(__("运费模板 %d 有 %d 条商品使用，不可删除"),
                    $transport_type_id, count($rows)));
            }
        }

        foreach ($ids as $transport_type_id) {
            $this->removeType($transport_type_id);
        }

        return true;
    }


    /**
     * 配送区域信息及运费
     * @param $transport_type_id
     * @param $district_id
     * @return array|mixed
     */
    public function getFreight($transport_type_id, $district_id)
    {
        $store_transport_type_item = [];
        if ($transport_type_id) {
            $store_transport_type = $this->repository->getOne($transport_type_id);
            if (!empty($store_transport_type)) {
                $store_transport_type_item = $store_transport_type;

                // 全部免运费，任何地区都配送
                $transport_type_free = $store_transport_type['transport_type_free'];
                if ($transport_type_free) {
                    $store_transport_item['transport_item_default_price'] = 0;
                    $store_transport_type_item['item'] = $store_transport_item;
                } else {
                    $item_query = ['transport_type_id' => $transport_type_id];
                    if (!empty($district_id)) {
                        $item_query[] = ['transport_item_city_ids', 'RAW', $district_id];
                    }
                    $store_transport_item = $this->storeTransportItemRepository->findOne($item_query);
                    $store_transport_type_item['item'] = $store_transport_item;
                }
            }
        }

        return $store_transport_type_item;
    }


    /**
     * 配送区域判断及运费计算，并修正最终数据
     * 收集运费模板及商品数量
     * 获取运费模板；
     * 检查运费模板是否统一；
     * 按计费模式计算运费（默认按件数计费）；
     * 返回运费金额。
     * @param array $store_items 店铺商品数据
     * @param int $district_id 配送地区 ID
     * @return array 运费
     * @throws ErrorException 如果运费规则不统一或配送区域异常
     */
    public function calTransportFreight(array $store_items, int $district_id): array
    {
        $data = [
            'freight' => 0,
            'transport_type_none_ids' => []
        ];

        // 收集运费模板及商品数量
        $tt_ids_map = $this->collectTransportTypeQuantities($store_items['items']);

        // 获取运费模板 && 检查运费模板是否统一
        $store_transport_types = $this->getStoreTransportTypes(array_keys($tt_ids_map));
        $this->validateTransportTypeRules($store_transport_types);

        // 判断运费计算方式
        $pricing_method = $store_transport_types[0]['transport_type_pricing_method'] ?? null;
        if ($pricing_method === 4) {
            // TODO: 如果需要按配送区域计费，可在此实现
        } else {
            // 按件数计费
            $data = $this->calculateFreightByQuantity($tt_ids_map, $store_transport_types, $district_id, $store_items['money_item_amount']);
        }

        return $data;
    }

    /**
     * 收集商品的运费模板 ID 和数量
     *
     * @param array $items 商品列表
     * @return array 运费模板 ID 与数量映射表
     */
    private function collectTransportTypeQuantities(array $items): array
    {
        $tt_ids_map = [];
        foreach ($items as $item) {
            $transport_type_id = $item['transport_type_id'];
            $tt_ids_map[$transport_type_id] = ($tt_ids_map[$transport_type_id] ?? 0) + $item['cart_quantity'];
        }

        return $tt_ids_map;
    }


    /**
     * 获取运费模板数据
     *
     * @param array $tt_ids 运费模板 ID 列表
     * @return array 运费模板数据
     * @throws ErrorException 如果未找到模板数据
     */
    private function getStoreTransportTypes(array $tt_ids): array
    {
        if (empty($tt_ids)) {
            throw new ErrorException(__("商品运费设置有误！请联系商家检查商品设置！"));
        }

        return $this->repository->gets($tt_ids);
    }


    /**
     * 验证运费模板规则是否统一
     *
     * @param array $store_transport_types 运费模板数据
     * @throws ErrorException 如果规则不统一
     */
    private function validateTransportTypeRules(array $store_transport_types): void
    {
        if (empty($store_transport_types)) {
            throw new ErrorException(__("运费模板不存在！"));
        }
        $pricing_methods = array_unique(array_column($store_transport_types, 'transport_type_pricing_method'));
        if (count($pricing_methods) > 1) {
            throw new ErrorException(__("所选商品运费模式不统一，请拆分下单！"));
        }
    }


    /**
     * 按件数计算运费
     * @param array $tt_ids_map 运费模板 ID 与数量映射表
     * @param array $store_transport_types 运费模板数据
     * @param int $district_id 配送地区 ID
     * @param float $money_item_amount 商品金额
     * @return array
     * @throws ErrorException
     */
    private function calculateFreightByQuantity(array $tt_ids_map, array $store_transport_types, int $district_id, float $money_item_amount): array
    {
        $freight = 0.0;
        $freight_free_amount_max = 0.0;
        $transport_type_none_ids = [];

        foreach ($tt_ids_map as $tt_id => $quantity) {
            $store_transport_type = $store_transport_types[$tt_id];
            $freight_free_amount = $store_transport_type['transport_type_freight_free'] ?? 0.0;
            $freight_free_amount_max = max($freight_free_amount_max, $freight_free_amount);

            $data = $this->calFreight($tt_id, $district_id, $quantity, $money_item_amount, $freight_free_amount);
            if (!$data['can_delivery']) {
                $transport_type_none_ids[] = $tt_id;
            } else {
                $freight += $data['freight'];
            }
        }

        return ['freight' => $freight, 'transport_type_none_ids' => $transport_type_none_ids];
    }


    /**
     * 运费计算方法
     * 如果订单包含多个货物，计算规则为：以最大基础运费为基础 + 每个商品的递增运费。
     *
     * @param int $transport_type_id 配送及运费模板 ID
     * @param int $district_id 用户所在区域 ID
     * @param int $quantity 购买数量
     * @param float $order_total 订单总金额
     * @param float $post_free_max 当前最大免运费金额
     * @return array 运费信息数组
     * @throws ErrorException 如果 transport_type_id 不合法
     */
    public function calFreight(int $transport_type_id, int $district_id, int $quantity, float $order_total, float $post_free_max): array
    {
        if (empty($transport_type_id)) {
            throw new ErrorException(__('商品运费模板有误'));
        }

        $data = [
            'can_delivery' => true,
            'freight_free_min' => $post_free_max,
            'freight' => 0.0
        ];

        // 获取配送区域及运费模板
        $store_transport_type_item = $this->getFreight($transport_type_id, $district_id);
        if (!empty($store_transport_type_item)) {
            $item = $store_transport_type_item['item'];

            // 判断配送区域是否可售
            if (!$store_transport_type_item['transport_type_free'] && empty($item)) {
                $data['can_delivery'] = false;
                return $data;
            }

            //全免运费
            if ($store_transport_type_item['transport_type_free']) {
                return $data;
            }

            if (!empty($item)) {
                $transport_type_freight_free = $store_transport_type_item['transport_type_freight_free'] ?? 0.0; //免运费额度
                $post_free_max = max($post_free_max, $transport_type_freight_free);
                $data['freight_free_min'] = $post_free_max;

                // 判断订单是否达到免运费金额
                if ($transport_type_freight_free > 0 && $order_total >= $transport_type_freight_free) {
                    return $data; // 免运费
                }

                // 默认配送数量与价格
                $default_num = $item['transport_item_default_num'] ?? 0;
                $add_num = max(0, $quantity - $default_num);

                $default_price = $item['transport_item_default_price'] ?? 0.0;
                $add_price = $item['transport_item_add_price'] ?? 0.0;
                $add_unit = $item['transport_item_add_num'] ?? 1;

                // 计算递增运费
                if ($add_num > 0 && $add_unit > 0) {
                    $incremental_freight = $add_price * ceil($add_num / $add_unit);
                    $data['freight'] = $default_price + $incremental_freight;
                } else {
                    // 仅基础运费
                    $data['freight'] = $default_price;
                }

                return $data;
            }
        }

        return $data;
    }

}
