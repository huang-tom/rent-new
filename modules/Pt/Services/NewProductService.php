<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\NewProductRentPeriodRepository;
use Modules\Pt\Repositories\Contracts\NewProductRentPriceRepository;
use Modules\Pt\Repositories\Contracts\NewProductRepository;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Sys\Services\NumberSeqService;

/**
 * Class NewProductService.
 *
 * @package Modules\Pt\Services
 */
class NewProductService extends BaseService
{
    /** 仅购买 */
    const SALE_MODE_BUY = 1;
    /** 仅租赁 */
    const SALE_MODE_RENT = 2;
    /** 购买+租赁 */
    const SALE_MODE_BUY_RENT = 3;

    /** 下架 */
    const PRODUCT_STATE_OFF = 0;
    /** 上架 */
    const PRODUCT_STATE_ON = 1;

    /** 待审核 */
    const AUDIT_STATUS_PENDING = 0;
    /** 审核通过 */
    const AUDIT_STATUS_PASSED = 1;

    /** 列表筛选-下架 */
    const LIST_STATUS_OFF = 0;
    /** 列表筛选-上架 */
    const LIST_STATUS_ON = 1;
    /** 列表筛选-待审核 */
    const LIST_STATUS_PENDING = 2;

    /** Redis 库存缓存 key 前缀 */
    const REDIS_STOCK_KEY_PREFIX = 'new_product:stock:';

    private $newProductRentPriceRepository;
    private $newProductRentPeriodRepository;
    private $productCategoryRepository;
    private $numberSeqService;

    public function __construct(
        NewProductRepository           $newProductRepository,
        NewProductRentPriceRepository  $newProductRentPriceRepository,
        NewProductRentPeriodRepository $newProductRentPeriodRepository,
        ProductCategoryRepository      $productCategoryRepository,
        NumberSeqService               $numberSeqService
    )
    {
        $this->repository = $newProductRepository;
        $this->newProductRentPriceRepository = $newProductRentPriceRepository;
        $this->newProductRentPeriodRepository = $newProductRentPeriodRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->numberSeqService = $numberSeqService;
    }

    /**
     * 列表（附带分类名、展示状态）
     */
    public function list($request, $criteria)
    {
        $data = parent::list($request, $criteria);
        if (empty($data['data'])) {
            return $data;
        }

        $category_ids = array_unique(array_filter(array_column($data['data'], 'category_id')));
        $category_map = [];
        if ($category_ids) {
            $categories = $this->productCategoryRepository->gets($category_ids) ?: [];
            foreach ($categories as $category) {
                $category_map[$category['category_id']] = $category['category_name'] ?? '';
            }
        }

        foreach ($data['data'] as $k => $row) {
            $data['data'][$k]['category_name'] = $category_map[$row['category_id']] ?? '';
            $data['data'][$k]['status_text'] = $this->formatStatusText($row);
            $data['data'][$k]['sale_mode_text'] = $this->formatSaleModeText($row['sale_mode'] ?? 0);
        }

        return $data;
    }

    /**
     * 顶部统计
     */
    public function getStatistics()
    {
        $base = [['is_deleted', '=', 0]];

        $total = (int)$this->repository->getNum($base);
        $buy_rent = (int)$this->repository->getNum(array_merge($base, [['sale_mode', '=', self::SALE_MODE_BUY_RENT]]));

        $buyable = (int)$this->repository->getNum(array_merge($base, [['sale_mode', 'IN', [self::SALE_MODE_BUY, self::SALE_MODE_BUY_RENT]]]));
        $buyable_on = (int)$this->repository->getNum(array_merge($base, [
            ['sale_mode', 'IN', [self::SALE_MODE_BUY, self::SALE_MODE_BUY_RENT]],
            ['product_state', '=', self::PRODUCT_STATE_ON],
            ['audit_status', '=', self::AUDIT_STATUS_PASSED],
        ]));

        $rentable = (int)$this->repository->getNum(array_merge($base, [['sale_mode', 'IN', [self::SALE_MODE_RENT, self::SALE_MODE_BUY_RENT]]]));
        $rentable_on = (int)$this->repository->getNum(array_merge($base, [
            ['sale_mode', 'IN', [self::SALE_MODE_RENT, self::SALE_MODE_BUY_RENT]],
            ['product_state', '=', self::PRODUCT_STATE_ON],
            ['audit_status', '=', self::AUDIT_STATUS_PASSED],
        ]));

        $pending = (int)$this->repository->getNum(array_merge($base, [['audit_status', '=', self::AUDIT_STATUS_PENDING]]));

        return [
            'total_count' => $total,
            'buy_rent_count' => $buy_rent,
            'buyable_count' => $buyable,
            'buyable_on_sale_count' => $buyable_on,
            'rentable_count' => $rentable,
            'rentable_on_sale_count' => $rentable_on,
            'pending_audit_count' => $pending,
        ];
    }

    /**
     * 新增或修改商品
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function saveProduct($request)
    {
        $product_id = (int)$request->input('product_id', 0);
        $sale_mode = (int)$request->input('sale_mode', self::SALE_MODE_BUY);
        $now = getDateTime();

        $product_row = $this->formatProductData($request, $sale_mode);
        $product_row['product_update_time'] = $now;

        DB::beginTransaction();

        try {
            if ($product_id) {
                $exist = $this->repository->getOne($product_id);
                if (!$exist || !empty($exist['is_deleted'])) {
                    throw new ErrorException('商品不存在');
                }
                $product_row['product_add_time'] = $exist['product_add_time'] ?? $now;
                // 编辑不改上下架、审核状态、商品编号
                $result = $this->repository->edit($product_id, $product_row);
            } else {
                $exist = null;
                $product_row['product_add_time'] = $now;
                $product_row['is_deleted'] = 0;
                $product_row['product_state'] = self::PRODUCT_STATE_OFF;
                $product_row['audit_status'] = self::AUDIT_STATUS_PENDING;
                $product_row['product_number'] = $this->numberSeqService->createNextSeq('PD');
                if (!$request->has('sale_num')) {
                    $product_row['sale_num'] = 0;
                }
                $result = $this->repository->add($product_row);
                if ($result) {
                    $product_id = (int)$result->getKey();
                }
            }

            if (!$result || !$product_id) {
                throw new ErrorException('商品保存失败');
            }

            // 含租赁时写入租期定价；仅购买则清空
            if (in_array($sale_mode, [self::SALE_MODE_RENT, self::SALE_MODE_BUY_RENT], true)) {
                $this->saveRentPrices($product_id, $request);
            } else {
                $this->clearRentPrices($product_id);
            }

            DB::commit();

            // 已上架商品编辑时，库存按增量调整 Redis（避免覆盖业务扣减）
            if ($exist && (int)($exist['product_state'] ?? 0) === self::PRODUCT_STATE_ON) {
                $old_stock = (int)($exist['stock_total'] ?? 0);
                $new_stock = (int)($product_row['stock_total'] ?? 0);
                $delta = $new_stock - $old_stock;
                if ($delta !== 0) {
                    $this->adjustProductStock($product_id, $delta, $new_stock);
                }
            }

            return ['product_id' => $product_id];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '操作失败');
        }
    }

    /**
     * 组装主表字段（不含上下架/审核状态）
     */
    private function formatProductData($request, $sale_mode)
    {
        $warehouse_ids = $request->input('warehouse_ids', '');
        if (is_array($warehouse_ids)) {
            $warehouse_ids = implode(',', array_filter($warehouse_ids, function ($v) {
                return $v !== '' && $v !== null;
            }));
        }

        $spec_json = $request->input('spec_json', '');
        if (is_array($spec_json)) {
            $spec_json = json_encode($spec_json, JSON_UNESCAPED_UNICODE);
        }

        $row = [
            'product_name' => $request->input('product_name', ''),
            'category_id' => (int)$request->input('category_id', 0),
            'warehouse_ids' => (string)$warehouse_ids,
            'product_image' => $request->input('product_image', ''),
            'product_intro' => $request->input('product_intro', ''),
            'sale_mode' => $sale_mode,
            'sale_price' => 0,
            'market_price' => 0,
            'unit' => '',
            'deposit' => 0,
            'daily_rent' => 0,
            'cycle_type' => 0,
            'settle_policy' => 0,
            'need_tier_price' => 0,
            'stock_total' => (int)$request->input('stock_total', 0),
            'stock_warning' => (int)$request->input('stock_warning', 0),
            'spec_json' => $spec_json ?: '',
        ];

        // 销量仅在明确传入时更新，避免编辑时被默认 0 覆盖
        if ($request->has('sale_num')) {
            $row['sale_num'] = (int)$request->input('sale_num', 0);
        }

        // 购买相关
        if (in_array($sale_mode, [self::SALE_MODE_BUY, self::SALE_MODE_BUY_RENT], true)) {
            $row['sale_price'] = $request->input('sale_price', 0);
            $row['market_price'] = $request->input('market_price', 0);
            $row['unit'] = $request->input('unit', '');
        }

        // 租赁相关
        if (in_array($sale_mode, [self::SALE_MODE_RENT, self::SALE_MODE_BUY_RENT], true)) {
            $row['deposit'] = $request->input('deposit', 0);
            $row['daily_rent'] = $request->input('daily_rent', 0);
            $row['cycle_type'] = (int)$request->input('cycle_type', 0);
            $row['settle_policy'] = (int)$request->input('settle_policy', 0);
            $row['need_tier_price'] = (int)$request->input('need_tier_price', 0);
        }

        return $row;
    }

    /**
     * 覆盖写入商品租期定价（金额由前端算好直接存）
     */
    private function saveRentPrices($product_id, $request)
    {
        $this->clearRentPrices($product_id);

        $rent_prices = $request->input('rent_prices', []);
        if (is_string($rent_prices)) {
            $rent_prices = json_decode($rent_prices, true) ?: [];
        }
        if (empty($rent_prices) || !is_array($rent_prices)) {
            return;
        }

        foreach ($rent_prices as $item) {
            $period_id = (int)($item['period_id'] ?? 0);
            if ($period_id <= 0) {
                continue;
            }
            $this->newProductRentPriceRepository->add([
                'product_id' => $product_id,
                'period_id' => $period_id,
                'total_rent' => $item['total_rent'] ?? 0,
                'daily_rent' => $item['daily_rent'] ?? 0,
            ]);
        }
    }

    /**
     * 清空商品租期定价
     */
    private function clearRentPrices($product_id)
    {
        $rows = $this->newProductRentPriceRepository->find(['product_id' => $product_id]);
        if (!$rows) {
            return;
        }
        $ids = array_column($rows, 'id');
        if ($ids) {
            $this->newProductRentPriceRepository->remove($ids);
        }
    }

    /**
     * 获取商品详情（编辑回显）
     * @param $product_id
     * @return array
     * @throws ErrorException
     */
    public function getProduct($product_id)
    {
        $product = $this->repository->getOne($product_id);
        if (!$product || !empty($product['is_deleted'])) {
            throw new ErrorException('商品不存在');
        }

        $rent_prices = $this->newProductRentPriceRepository->find(['product_id' => $product_id]) ?: [];
        $period_map = [];
        if ($rent_prices) {
            $period_ids = array_column($rent_prices, 'period_id');
            $periods = $this->newProductRentPeriodRepository->find([['period_id', 'IN', $period_ids]]) ?: [];
            foreach ($periods as $period) {
                $period_map[$period['period_id']] = $period;
            }
        }

        $rent_price_list = [];
        foreach ($rent_prices as $row) {
            $period = $period_map[$row['period_id']] ?? null;
            $rent_price_list[] = [
                'id' => $row['id'],
                'period_id' => $row['period_id'],
                'period_name' => $period['period_name'] ?? '',
                'period_days' => $period ? (int)$period['period_days'] : 0,
                'total_rent' => $row['total_rent'] ?? 0,
                'daily_rent' => $row['daily_rent'] ?? 0,
            ];
        }

        $product['warehouse_id_list'] = $product['warehouse_ids'] !== ''
            ? array_values(array_filter(explode(',', $product['warehouse_ids']), function ($v) {
                return $v !== '';
            }))
            : [];
        $product['spec_list'] = [];
        if (!empty($product['spec_json'])) {
            $decoded = json_decode($product['spec_json'], true);
            $product['spec_list'] = is_array($decoded) ? $decoded : [];
        }
        $product['rent_prices'] = $rent_price_list;
        $product['status_text'] = $this->formatStatusText($product);
        $product['sale_mode_text'] = $this->formatSaleModeText($product['sale_mode'] ?? 0);

        if (!empty($product['category_id'])) {
            $category = $this->productCategoryRepository->getOne($product['category_id']);
            $product['category_name'] = $category['category_name'] ?? '';
        } else {
            $product['category_name'] = '';
        }

        return $product;
    }

    /**
     * 上下架（须已审核通过）
     * @param int $product_id
     * @param int $product_state 1上架 0下架
     * @return bool
     * @throws ErrorException
     */
    public function editState($product_id, $product_state)
    {
        $product = $this->getExistProduct($product_id);
        $product_state = (int)$product_state;

        if (!in_array($product_state, [self::PRODUCT_STATE_OFF, self::PRODUCT_STATE_ON], true)) {
            throw new ErrorException('上下架状态不正确');
        }

        if ($product_state === self::PRODUCT_STATE_ON
            && (int)$product['audit_status'] !== self::AUDIT_STATUS_PASSED) {
            throw new ErrorException('商品未审核通过，不能上架');
        }

        $result = $this->repository->edit($product_id, [
            'product_state' => $product_state,
            'product_update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('更新失败');
        }

        // 上架：库存写入 Redis；下架：清除缓存
        if ($product_state === self::PRODUCT_STATE_ON) {
            $this->cacheProductStock($product_id, (int)($product['stock_total'] ?? 0));
        } else {
            $this->forgetProductStock($product_id);
        }

        return true;
    }

    /**
     * 审核通过
     * @param int $product_id
     * @return bool
     * @throws ErrorException
     */
    public function audit($product_id)
    {
        $product = $this->getExistProduct($product_id);
        if ((int)$product['audit_status'] === self::AUDIT_STATUS_PASSED) {
            throw new ErrorException('商品已审核通过');
        }

        $result = $this->repository->edit($product_id, [
            'audit_status' => self::AUDIT_STATUS_PASSED,
            'product_update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('审核失败');
        }

        return true;
    }

    /**
     * 批量上下架
     * @param array $product_ids
     * @param int $product_state
     * @return int 成功数量
     * @throws ErrorException
     */
    public function batchEditState(array $product_ids, $product_state)
    {
        $product_ids = array_values(array_filter(array_map('intval', $product_ids)));
        if (!$product_ids) {
            throw new ErrorException('请选择商品');
        }

        $product_state = (int)$product_state;
        if (!in_array($product_state, [self::PRODUCT_STATE_OFF, self::PRODUCT_STATE_ON], true)) {
            throw new ErrorException('上下架状态不正确');
        }

        $success = 0;
        foreach ($product_ids as $product_id) {
            try {
                $this->editState($product_id, $product_state);
                $success++;
            } catch (ErrorException $e) {
                // 跳过不符合条件的商品
                continue;
            }
        }

        return $success;
    }

    /**
     * 批量审核
     * @param array $product_ids
     * @return int
     * @throws ErrorException
     */
    public function batchAudit(array $product_ids)
    {
        $product_ids = array_values(array_filter(array_map('intval', $product_ids)));
        if (!$product_ids) {
            throw new ErrorException('请选择商品');
        }

        $success = 0;
        foreach ($product_ids as $product_id) {
            try {
                $this->audit($product_id);
                $success++;
            } catch (ErrorException $e) {
                continue;
            }
        }

        return $success;
    }

    /**
     * 删除商品（软删除，仅标记，不删库）
     * @param $product_id
     * @return bool
     * @throws ErrorException
     */
    public function removeProduct($product_id)
    {
        $this->getExistProduct($product_id);

        $result = $this->repository->edit($product_id, [
            'is_deleted' => 1,
            'product_state' => self::PRODUCT_STATE_OFF,
            'product_update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('删除失败');
        }

        $this->forgetProductStock($product_id);

        return true;
    }

    /**
     * @param $product_id
     * @return array
     * @throws ErrorException
     */
    private function getExistProduct($product_id)
    {
        $product = $this->repository->getOne($product_id);
        if (!$product || !empty($product['is_deleted'])) {
            throw new ErrorException('商品不存在');
        }

        return $product;
    }

    /**
     * 定时补齐：已上架商品若 Redis 库存缺失则重新写入
     * @return array{total:int,missing:int,fixed:int}
     */
    public function syncOnShelfStockCache()
    {
        $products = $this->repository->find([
            ['is_deleted', '=', 0],
            ['product_state', '=', self::PRODUCT_STATE_ON],
        ]) ?: [];

        $total = count($products);
        $missing = 0;
        $fixed = 0;

        foreach ($products as $product) {
            $product_id = (int)$product['product_id'];
            $key = $this->getStockCacheKey($product_id);
            if (Redis::exists($key)) {
                continue;
            }
            $missing++;
            $this->cacheProductStock($product_id, (int)($product['stock_total'] ?? 0));
            $fixed++;
        }

        return [
            'total' => $total,
            'missing' => $missing,
            'fixed' => $fixed,
        ];
    }

    /**
     * 全量刷新：已上架商品库存全部重新 SET 到 Redis（以库表为准）
     * @return array{total:int,fixed:int}
     */
    public function refreshOnShelfStockCache()
    {
        $products = $this->repository->find([
            ['is_deleted', '=', 0],
            ['product_state', '=', self::PRODUCT_STATE_ON],
        ]) ?: [];

        $fixed = 0;
        foreach ($products as $product) {
            $this->cacheProductStock(
                (int)$product['product_id'],
                (int)($product['stock_total'] ?? 0)
            );
            $fixed++;
        }

        return [
            'total' => count($products),
            'fixed' => $fixed,
        ];
    }

    /**
     * Redis 库存 key：new_product:stock:{product_id}
     */
    private function getStockCacheKey($product_id)
    {
        return self::REDIS_STOCK_KEY_PREFIX . (int)$product_id;
    }

    /**
     * 上架时将库存写入 Redis（值：stock_total）
     */
    private function cacheProductStock($product_id, $stock_total)
    {
        try {
            Redis::set($this->getStockCacheKey($product_id), (int)$stock_total);
        } catch (\Exception $e) {
            throw new ErrorException('库存写入缓存失败：' . ($e->getMessage() ?: 'Redis异常'));
        }
    }

    /**
     * 编辑库存：对 Redis 做增量（加/减），避免覆盖并发扣减
     * key 不存在时按新库存绝对值写入
     * @param int $product_id
     * @param int $delta 新库存 - 旧库存（可正可负）
     * @param int $fallbackAbsolute key 缺失时写入的绝对值
     */
    private function adjustProductStock($product_id, $delta, $fallbackAbsolute)
    {
        $delta = (int)$delta;
        if ($delta === 0) {
            return;
        }

        try {
            $key = $this->getStockCacheKey($product_id);
            if (!Redis::exists($key)) {
                Redis::set($key, max(0, (int)$fallbackAbsolute));
                return;
            }

            $after = (int)Redis::incrby($key, $delta);
            // 减库存后不允许为负
            if ($after < 0) {
                Redis::set($key, 0);
            }
        } catch (\Exception $e) {
            throw new ErrorException('库存缓存调整失败：' . ($e->getMessage() ?: 'Redis异常'));
        }
    }

    /**
     * 已付款下单扣库存：先原子扣 Redis，不足则报错；同时扣商品表 stock_total
     * @param array $items 明细，需含 product_id、quantity，可选 product_name
     * @return array 已扣减列表，事务失败时用于回补 Redis
     */
    public function deductOrderStock(array $items)
    {
        $need = [];
        foreach ($items as $item) {
            $product_id = (int)($item['product_id'] ?? 0);
            $qty = (int)($item['quantity'] ?? 0);
            $name = trim((string)($item['product_name'] ?? ''));
            if ($product_id <= 0) {
                throw new ErrorException(($name !== '' ? '商品【' . $name . '】' : '商品') . '缺少商品ID，无法扣减库存');
            }
            if ($qty <= 0) {
                continue;
            }
            if (!isset($need[$product_id])) {
                $need[$product_id] = [
                    'product_id' => $product_id,
                    'product_name' => $name,
                    'quantity' => 0,
                ];
            }
            $need[$product_id]['quantity'] += $qty;
            if ($name !== '') {
                $need[$product_id]['product_name'] = $name;
            }
        }

        $deducted = [];
        try {
            foreach ($need as $row) {
                $product = $this->getExistProduct($row['product_id']);
                $name = $row['product_name'] !== '' ? $row['product_name'] : ($product['product_name'] ?? '');
                $this->decrRedisStock($row['product_id'], $row['quantity'], $name);
                $deducted[] = [
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                ];
                $affected = DB::table('pt_new_product')
                    ->where('product_id', $row['product_id'])
                    ->where('is_deleted', 0)
                    ->where('stock_total', '>=', $row['quantity'])
                    ->decrement('stock_total', $row['quantity'], [
                        'product_update_time' => getDateTime(),
                    ]);
                if (!$affected) {
                    throw new ErrorException('商品【' . $name . '】库存不足');
                }
            }
        } catch (\Exception $e) {
            $this->restoreOrderStock($deducted);
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '扣减库存失败');
        }

        return $deducted;
    }

    /**
     * 只回补 Redis。商品已下架、缓存键不存在时不新建键。
     */
    public function restoreRedisStock(array $deducted)
    {
        foreach ($deducted as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }
            try {
                $key = $this->getStockCacheKey($product_id);
                if (Redis::exists($key)) {
                    Redis::incrby($key, $qty);
                }
            } catch (\Exception $e) {
                throw new ErrorException('库存回补失败：' . ($e->getMessage() ?: 'Redis异常'));
            }
        }
    }

    /**
     * 超时释放：同时回补商品表库存和 Redis（商品已下架则只回表，不新建缓存键）
     */
    public function restoreDeductedStock(array $deducted)
    {
        foreach ($deducted as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }
            DB::table('pt_new_product')
                ->where('product_id', $product_id)
                ->increment('stock_total', $qty, [
                    'product_update_time' => getDateTime(),
                ]);
        }
        $this->restoreRedisStock($deducted);
    }

    /**
     * 事务失败时回补已扣的 Redis 库存（库表由事务回滚）
     */
    public function restoreOrderStock(array $deducted)
    {
        foreach ($deducted as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }
            try {
                Redis::incrby($this->getStockCacheKey($product_id), $qty);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Redis 原子扣减。无缓存或数量不够时不改库存并报错。
     * 返回 -1 表示 key 不存在，-2 表示不足，否则返回扣减后的库存。
     */
    private function decrRedisStock($product_id, $qty, $product_name)
    {
        $key = $this->getStockCacheKey($product_id);
        $label = $product_name !== '' ? '商品【' . $product_name . '】' : '商品';
        $script = <<<'LUA'
local stock = redis.call('GET', KEYS[1])
if not stock then
    return -1
end
stock = tonumber(stock)
local qty = tonumber(ARGV[1])
if not stock or not qty or stock < qty then
    return -2
end
return redis.call('DECRBY', KEYS[1], qty)
LUA;
        try {
            $after = Redis::eval($script, 1, $key, (int)$qty);
            if ($after === null || (int)$after < 0) {
                throw new ErrorException($label . '库存不足');
            }
        } catch (ErrorException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ErrorException('库存扣减失败：' . ($e->getMessage() ?: 'Redis异常'));
        }
    }

    /**
     * 下架时清除 Redis 库存缓存
     */
    private function forgetProductStock($product_id)
    {
        try {
            Redis::del($this->getStockCacheKey($product_id));
        } catch (\Exception $e) {
            throw new ErrorException('库存缓存清除失败：' . ($e->getMessage() ?: 'Redis异常'));
        }
    }

    private function formatStatusText($row)
    {
        if ((int)($row['audit_status'] ?? 0) === self::AUDIT_STATUS_PENDING) {
            return '待审核';
        }
        return ((int)($row['product_state'] ?? 0) === self::PRODUCT_STATE_ON) ? '上架' : '下架';
    }

    private function formatSaleModeText($sale_mode)
    {
        $map = [
            self::SALE_MODE_BUY => '仅购买',
            self::SALE_MODE_RENT => '仅租赁',
            self::SALE_MODE_BUY_RENT => '购买+租赁',
        ];

        return $map[(int)$sale_mode] ?? '';
    }
}
