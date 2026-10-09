<?php

namespace Modules\Marketing\Services;

use App\Support\StateCode;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use Modules\Marketing\Repositories\Contracts\ActivityItemRepository;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;

/**
 * Class ActivityItemService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityItemService extends BaseService
{

    private $activityBaseRepository;
    private $productItemRepository;
    private $productBaseRepository;
    private $productIndexRepository;
    private $userInfoRepository;

    public function __construct(
        ActivityItemRepository $activityItemRepository,
        ActivityBaseRepository $activityBaseRepository,
        ProductItemRepository  $productItemRepository,
        ProductBaseRepository  $productBaseRepository,
        ProductIndexRepository $productIndexRepository,
        UserInfoRepository     $userInfoRepository
    )
    {
        $this->repository = $activityItemRepository;
        $this->activityBaseRepository = $activityBaseRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
        $this->userInfoRepository = $userInfoRepository;
    }

    /**
     * 活动商品列表
     * [本地补齐 2026-09-22] 厂商原实现是 `return [];` 空壳，
     * 前端活动商品页永远是空表。此处按 marketing_activity_item 关联
     * pt_product_base / pt_product_item 取回商品名与SKU原价，便于前端展示折扣。
     */
    public function getActivityBuyItems($activity_id): array
    {
        if (!$activity_id) {
            return [];
        }

        $rows = DB::table('marketing_activity_item as ai')
            ->leftJoin('pt_product_base as pb', 'pb.product_id', '=', 'ai.product_id')
            ->leftJoin('pt_product_item as pi', 'pi.item_id', '=', 'ai.item_id')
            ->where('ai.activity_id', '=', $activity_id)
            ->orderBy('ai.activity_item_id', 'ASC')
            ->get([
                'ai.activity_item_id',
                'ai.activity_id',
                'ai.activity_type_id',
                'ai.product_id',
                'ai.item_id',
                'ai.category_id',
                'ai.activity_item_starttime',
                'ai.activity_item_endtime',
                'ai.activity_item_price',
                'ai.activity_item_min_quantity',
                'ai.activity_item_state',
                'ai.activity_item_recommend',
                'pb.product_name',
                'pi.item_name',
                'pi.item_unit_price',
                'pi.item_quantity',
            ]);

        return array_map(static function ($row) {
            $arr = (array)$row;
            $arr['item_unit_price'] = isset($arr['item_unit_price']) ? (float)$arr['item_unit_price'] : 0;
            $arr['activity_item_price'] = isset($arr['activity_item_price']) ? (float)$arr['activity_item_price'] : 0;
            $arr['activity_item_min_quantity'] = isset($arr['activity_item_min_quantity']) ? (int)$arr['activity_item_min_quantity'] : 0;
            $arr['activity_item_state'] = isset($arr['activity_item_state']) ? (int)$arr['activity_item_state'] : 0;
            return $arr;
        }, $rows->toArray());
    }

    /**
     * 批量添加活动商品
     * [本地补齐 2026-09-22] 厂商原实现是 `return true;` 空壳（点"添加商品"永远不落库）。
     *
     * 入参约定（本地定义，厂商无参考实现）：
     *   activity_id                    必填
     *   item_ids                       必填，商品SKU编号数组（也接受逗号分隔字符串）
     *   activity_item_price            可选，统一活动价；缺省取该SKU原价
     *   activity_item_min_quantity     可选，起购量；缺省 1
     *
     * @return int 实际新增条数
     */
    public function addActivityBuyItems($request, &$msg)
    {
        $activity_id = (int)$request->input('activity_id', 0);
        if (!$activity_id) {
            throw new ErrorException(__('活动编号不能为空'));
        }

        $activity = DB::table('marketing_activity_base')->where('activity_id', $activity_id)->first();
        if (!$activity) {
            throw new ErrorException(__('活动不存在'));
        }

        $item_ids = $request->input('item_ids', []);
        if (is_string($item_ids)) {
            $item_ids = explode(',', $item_ids);
        }
        $item_ids = array_values(array_unique(array_filter(array_map('intval', (array)$item_ids))));
        if (empty($item_ids)) {
            throw new ErrorException(__('请选择要加入活动的商品'));
        }

        $price = $request->input('activity_item_price', null);
        $price = ($price === null || $price === '') ? null : round((float)$price, 2);
        $min_quantity = (int)$request->input('activity_item_min_quantity', 1);

        // 已在该活动里的 SKU 直接跳过（保证按钮可重复点击而不报唯一键冲突）
        $exists = array_map('intval', DB::table('marketing_activity_item')
            ->where('activity_id', $activity_id)
            ->whereIn('item_id', $item_ids)
            ->pluck('item_id')
            ->toArray());

        $skus = DB::table('pt_product_item')
            ->whereIn('item_id', $item_ids)
            ->get(['item_id', 'product_id', 'category_id', 'store_id', 'item_unit_price']);

        if ($skus->isEmpty()) {
            throw new ErrorException(__('所选商品SKU不存在'));
        }

        $rows = [];
        $skipped = 0;
        foreach ($skus as $sku) {
            if (in_array((int)$sku->item_id, $exists, true)) {
                $skipped++;
                continue;
            }
            $rows[] = [
                'store_id' => (int)$sku->store_id,
                'activity_type_id' => (int)$activity->activity_type_id,
                'activity_id' => $activity_id,
                'product_id' => (int)$sku->product_id,
                'item_id' => (int)$sku->item_id,
                'category_id' => (int)$sku->category_id,
                'activity_item_starttime' => (int)$activity->activity_starttime,
                'activity_item_endtime' => (int)$activity->activity_endtime,
                'activity_item_price' => $price === null ? (float)$sku->item_unit_price : $price,
                'activity_item_min_quantity' => $min_quantity,
                'activity_item_state' => 1,
                'activity_item_recommend' => 0,
            ];
        }

        if (!empty($rows)) {
            DB::table('marketing_activity_item')->insert($rows);
        }

        $msg = $skipped > 0 ? '已存在并跳过 ' . $skipped . ' 个SKU' : '';

        return count($rows);
    }

    public function addDiscountItem($activity_id, $product_items, $activity_base)
    {
        return true;
    }

    public function editActivityTypeIds($activity_base, $product_ids)
    {
        $flag_row = [];
        return is_ok($flag_row);
    }

    public function checkItem($item_ids, &$msg = '')
    {
        return $item_ids;
    }

    /**
     * 修改单个活动商品
     * [本地补齐 2026-09-22] 厂商原实现是 `return true;` 空壳。
     * 入参：activity_item_id（必填）+ 需更新的字段（只更新实际传了的）
     */
    public function editActivityItem($request): mixed
    {
        $activity_item_id = (int)$request->input('activity_item_id', 0);
        if (!$activity_item_id) {
            throw new ErrorException(__('活动商品编号不能为空'));
        }

        $data = [];
        if ($request->has('activity_item_price')) {
            $data['activity_item_price'] = round((float)$request['activity_item_price'], 2);
        }
        if ($request->has('activity_item_min_quantity')) {
            $data['activity_item_min_quantity'] = (int)$request['activity_item_min_quantity'];
        }
        if ($request->has('activity_item_state')) {
            $data['activity_item_state'] = (int)$request['activity_item_state'];
        }
        if ($request->has('activity_item_recommend')) {
            $data['activity_item_recommend'] = $request->boolean('activity_item_recommend') ? 1 : 0;
        }
        if ($request->has('activity_item_starttime')) {
            $data['activity_item_starttime'] = (int)$request['activity_item_starttime'];
        }
        if ($request->has('activity_item_endtime')) {
            $data['activity_item_endtime'] = (int)$request['activity_item_endtime'];
        }

        if (empty($data)) {
            throw new ErrorException(__('没有需要更新的字段'));
        }

        $affected = DB::table('marketing_activity_item')
            ->where('activity_item_id', $activity_item_id)
            ->update($data);

        if ($affected === false) {
            throw new ErrorException(__('更新失败'));
        }

        return true;
    }

    /**
     * 移除活动商品
     * [本地补齐 2026-09-22] 厂商原实现是 `return true;` 空壳。
     * 入参：activity_item_ids（数组）或 activity_item_id（单个）
     */
    public function removeItem($request): bool
    {
        $ids = $request->input('activity_item_ids', null);
        if (empty($ids)) {
            $one = (int)$request->input('activity_item_id', 0);
            $ids = $one ? [$one] : [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
        if (empty($ids)) {
            throw new ErrorException(__('请选择要移除的活动商品'));
        }

        DB::table('marketing_activity_item')->whereIn('activity_item_id', $ids)->delete();

        return true;
    }

    public function getActivityInfo($item_ids, $user_id = 0): array
    {
        $data = [];

        return $data;
    }

    /**
     * 统一设置活动商品折扣
     * [本地补齐 2026-09-22] 厂商原实现是 `return true;` 空壳。
     * 入参：activity_id、discount（折扣百分比，90 表示 9 折）
     * 以 pt_product_item.item_unit_price 为基准价重算 activity_item_price。
     */
    public function editBatchPrice($activity_id, $discount): bool
    {
        $activity_id = (int)$activity_id;
        $discount = (float)$discount;

        if (!$activity_id) {
            throw new ErrorException(__('活动编号不能为空'));
        }
        if ($discount <= 0 || $discount > 100) {
            throw new ErrorException(__('折扣需在 0~100 之间（90 表示 9 折）'));
        }

        $items = DB::table('marketing_activity_item as ai')
            ->leftJoin('pt_product_item as pi', 'pi.item_id', '=', 'ai.item_id')
            ->where('ai.activity_id', $activity_id)
            ->get(['ai.activity_item_id', 'pi.item_unit_price']);

        if ($items->isEmpty()) {
            throw new ErrorException(__('该活动下还没有商品，无法批量改价'));
        }

        foreach ($items as $item) {
            $base = (float)$item->item_unit_price;
            DB::table('marketing_activity_item')
                ->where('activity_item_id', $item->activity_item_id)
                ->update(['activity_item_price' => round($base * $discount / 100, 2)]);
        }

        return true;
    }

    public function getNormalActivityItems($item_ids = [])
    {
        return [];
    }


}
