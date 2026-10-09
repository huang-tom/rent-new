<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Modules\Pt\Services\NewProductService;
use Modules\Trade\Repositories\Contracts\NewOrderStockLockRepository;

/**
 * 购买 / 租赁订单库存占用
 */
class NewOrderStockLockService
{
    const TYPE_BUY = 1;
    const TYPE_RENT = 2;

    /** 占用未处理 */
    const HANDLE_PENDING = 0;
    /** 占用已处理 */
    const HANDLE_DONE = 1;

    const LOCK_MINUTES = 5;
    const CLOSE_MINUTES = 30;

    private $lockRepository;
    private $newProductService;

    public function __construct(
        NewOrderStockLockRepository $lockRepository,
        NewProductService           $newProductService
    )
    {
        $this->lockRepository = $lockRepository;
        $this->newProductService = $newProductService;
    }

    /**
     * 库存足够才扣减并写入占用记录。已支付的占用直接标已处理。
     * @return array 已扣减明细，供外层事务失败时回补 Redis
     */
    public function occupy($order_type, $order_id, $order_number, array $items, $already_paid)
    {
        $deducted = $this->newProductService->deductOrderStock($items);
        $now = getDateTime();
        try {
            foreach ($deducted as $row) {
                $name = '';
                foreach ($items as $item) {
                    if ((int)($item['product_id'] ?? 0) === (int)$row['product_id']) {
                        $name = (string)($item['product_name'] ?? '');
                        break;
                    }
                }
                $this->lockRepository->add([
                    'order_type' => (int)$order_type,
                    'order_id' => (int)$order_id,
                    'order_number' => (string)$order_number,
                    'product_id' => (int)$row['product_id'],
                    'product_name' => $name,
                    'quantity' => (int)$row['quantity'],
                    'lock_time' => $now,
                    'handle_status' => $already_paid ? self::HANDLE_DONE : self::HANDLE_PENDING,
                    'handle_time' => $already_paid ? $now : null,
                    'handle_remark' => $already_paid ? '下单已支付' : '',
                    'add_time' => $now,
                ]);
            }
        } catch (\Exception $e) {
            $this->newProductService->restoreOrderStock($deducted);
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '库存占用失败');
        }

        return $deducted;
    }

    /**
     * 每分钟：超过 5 分钟未支付则释放库存；已支付则只把占用标为已处理
     */
    public function releaseExpiredLocks()
    {
        $deadline = date('Y-m-d H:i:s', time() - self::LOCK_MINUTES * 60);
        $rows = DB::table('trade_new_order_stock_lock')
            ->where('handle_status', self::HANDLE_PENDING)
            ->orderBy('lock_id', 'ASC')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $row = (array)$row;
            $key = (int)$row['order_type'] . ':' . (int)$row['order_id'];
            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }
            $groups[$key][] = $row;
        }

        $paid_count = 0;
        $release_count = 0;
        foreach ($groups as $group) {
            $result = $this->handleLockGroup($group, $deadline);
            if ($result === 'paid') {
                $paid_count++;
            } elseif ($result === 'released') {
                $release_count++;
            }
        }

        return [
            'paid' => $paid_count,
            'released' => $release_count,
        ];
    }

    /**
     * 每分钟：待支付超过 30 分钟仍未支付则关闭购买单和租赁单
     */
    public function closeUnpaidOrders()
    {
        $deadline = date('Y-m-d H:i:s', time() - self::CLOSE_MINUTES * 60);
        $buy_count = $this->closeOrders(
            'trade_new_order',
            'trade_new_order_log',
            NewOrderService::STATUS_PENDING,
            NewOrderService::STATUS_CLOSED,
            $deadline
        );
        $rent_count = $this->closeOrders(
            'trade_new_rent_order',
            'trade_new_rent_order_log',
            NewRentOrderService::STATUS_PENDING,
            NewRentOrderService::STATUS_CLOSED,
            $deadline
        );

        return [
            'buy' => $buy_count,
            'rent' => $rent_count,
        ];
    }

    private function handleLockGroup(array $group, $deadline)
    {
        $first = $group[0];
        $order_type = (int)$first['order_type'];
        $order_id = (int)$first['order_id'];
        $order = $this->findOrder($order_type, $order_id);
        if (!$order) {
            $this->releaseMissingOrder($group);
            return 'released';
        }

        if ($this->isPaid($order_type, (int)$order['order_status'])) {
            $this->markLocks($group, '已支付');
            if ((int)($order['stock_locked'] ?? 0) !== 1) {
                DB::table($this->orderTable($order_type))
                    ->where('order_id', $order_id)
                    ->update([
                        'stock_locked' => 1,
                        'update_time' => getDateTime(),
                    ]);
            }
            return 'paid';
        }

        $lock_time = $first['lock_time'] ?? '';
        if ($lock_time === '' || $lock_time > $deadline) {
            return '';
        }

        $now = getDateTime();
        $redis_done = [];
        DB::beginTransaction();
        try {
            $lock_ids = array_column($group, 'lock_id');
            $affected = DB::table('trade_new_order_stock_lock')
                ->whereIn('lock_id', $lock_ids)
                ->where('handle_status', self::HANDLE_PENDING)
                ->update([
                    'handle_status' => self::HANDLE_DONE,
                    'handle_time' => $now,
                    'handle_remark' => '超时未支付，释放库存',
                ]);
            if ((int)$affected !== count($lock_ids)) {
                DB::rollBack();
                return '';
            }
            $this->restoreTableStock($group, $now);
            DB::table($this->orderTable($order_type))
                ->where('order_id', $order_id)
                ->update([
                    'stock_locked' => 0,
                    'update_time' => $now,
                ]);
            $this->addOrderLog($order_type, $order_id, '超过5分钟未支付，释放锁定库存', $now);
            $this->applyRedisRestore($group, $redis_done);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->rollbackRedisStock($redis_done);
            throw $e;
        }

        return 'released';
    }

    private function releaseMissingOrder(array $group)
    {
        $now = getDateTime();
        $redis_done = [];
        DB::beginTransaction();
        try {
            $lock_ids = array_column($group, 'lock_id');
            $affected = DB::table('trade_new_order_stock_lock')
                ->whereIn('lock_id', $lock_ids)
                ->where('handle_status', self::HANDLE_PENDING)
                ->update([
                    'handle_status' => self::HANDLE_DONE,
                    'handle_time' => $now,
                    'handle_remark' => '订单不存在，释放库存',
                ]);
            if ((int)$affected !== count($lock_ids)) {
                DB::rollBack();
                return;
            }
            $this->restoreTableStock($group, $now);
            $this->applyRedisRestore($group, $redis_done);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->rollbackRedisStock($redis_done);
            throw $e;
        }
    }

    private function applyRedisRestore(array $group, array &$done)
    {
        foreach ($group as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }
            $key = NewProductService::REDIS_STOCK_KEY_PREFIX . $product_id;
            if (!Redis::exists($key)) {
                continue;
            }
            Redis::incrby($key, $qty);
            $done[] = $row;
        }
    }

    private function restoreTableStock(array $group, $now)
    {
        foreach ($group as $row) {
            DB::table('pt_new_product')
                ->where('product_id', (int)$row['product_id'])
                ->increment('stock_total', (int)$row['quantity'], [
                    'product_update_time' => $now,
                ]);
        }
    }

    private function rollbackRedisStock(array $group)
    {
        foreach ($group as $row) {
            $product_id = (int)($row['product_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($product_id <= 0 || $qty <= 0) {
                continue;
            }
            try {
                $key = NewProductService::REDIS_STOCK_KEY_PREFIX . $product_id;
                if (Redis::exists($key)) {
                    Redis::decrby($key, $qty);
                }
            } catch (\Exception $e) {
            }
        }
    }

    private function markLocks(array $group, $remark)
    {
        $now = getDateTime();
        DB::table('trade_new_order_stock_lock')
            ->whereIn('lock_id', array_column($group, 'lock_id'))
            ->where('handle_status', self::HANDLE_PENDING)
            ->update([
                'handle_status' => self::HANDLE_DONE,
                'handle_time' => $now,
                'handle_remark' => $remark,
            ]);
    }

    private function closeOrders($order_table, $log_table, $pending_status, $closed_status, $deadline)
    {
        $orders = DB::table($order_table)
            ->where('is_deleted', 0)
            ->where('order_status', $pending_status)
            ->where('order_time', '<=', $deadline)
            ->get();

        $count = 0;
        $now = getDateTime();
        foreach ($orders as $order) {
            $order = (array)$order;
            $affected = DB::table($order_table)
                ->where('order_id', (int)$order['order_id'])
                ->where('order_status', $pending_status)
                ->update([
                    'order_status' => $closed_status,
                    'update_time' => $now,
                ]);
            if (!$affected) {
                continue;
            }
            DB::table($log_table)->insert([
                'order_id' => (int)$order['order_id'],
                'log_content' => '超过30分钟未支付，关闭订单',
                'operator_name' => '系统',
                'add_time' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function findOrder($order_type, $order_id)
    {
        $row = DB::table($this->orderTable($order_type))->where('order_id', $order_id)->first();
        return $row ? (array)$row : null;
    }

    private function orderTable($order_type)
    {
        return (int)$order_type === self::TYPE_RENT ? 'trade_new_rent_order' : 'trade_new_order';
    }

    private function isPaid($order_type, $order_status)
    {
        if ((int)$order_type === self::TYPE_RENT) {
            return !in_array((int)$order_status, [
                NewRentOrderService::STATUS_PENDING,
                NewRentOrderService::STATUS_CLOSED,
            ], true);
        }

        return !in_array((int)$order_status, [
            NewOrderService::STATUS_PENDING,
            NewOrderService::STATUS_CLOSED,
        ], true);
    }

    private function addOrderLog($order_type, $order_id, $content, $now)
    {
        $table = (int)$order_type === self::TYPE_RENT ? 'trade_new_rent_order_log' : 'trade_new_order_log';
        DB::table($table)->insert([
            'order_id' => (int)$order_id,
            'log_content' => $content,
            'operator_name' => '系统',
            'add_time' => $now,
        ]);
    }
}
