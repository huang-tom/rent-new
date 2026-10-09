<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Modules\Pt\Services\NewProductService;

/**
 * 新订单支付回调：order_id 传订单号，PO 购买、RO 租赁
 */
class NewOrderPayService
{
    private $stockLockService;
    private $newProductService;

    public function __construct(
        NewOrderStockLockService $stockLockService,
        NewProductService        $newProductService
    )
    {
        $this->stockLockService = $stockLockService;
        $this->newProductService = $newProductService;
    }

    /**
     * @param string $order_number 订单号
     * @param array $pay 支付信息：paid_amount / pay_method / pay_time / trade_no
     */
    public function setPaidYes($order_number, array $pay = [])
    {
        $order_number = trim((string)$order_number);
        if ($order_number === '') {
            throw new ErrorException('订单编号不能为空');
        }

        $order_type = $this->resolveOrderType($order_number);
        $table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order'
            : 'trade_new_order';
        $item_table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order_item'
            : 'trade_new_order_item';
        $log_table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order_log'
            : 'trade_new_order_log';
        $pending = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PENDING
            : NewOrderService::STATUS_PENDING;
        $paid_status = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PAID
            : NewOrderService::STATUS_PAID;

        $order = DB::table($table)
            ->where('order_number', $order_number)
            ->where('is_deleted', 0)
            ->first();
        if (!$order) {
            throw new ErrorException('订单信息不存在');
        }
        $order = (array)$order;

        if ((int)$order['order_status'] === $paid_status) {
            return true;
        }
        if ((int)$order['order_status'] !== $pending) {
            throw new ErrorException('未更改到符合条件的订单');
        }

        $now = getDateTime();
        $pay_time = $this->normalizeDateTime($pay['pay_time'] ?? null) ?: $now;
        $paid_amount = array_key_exists('paid_amount', $pay) && $pay['paid_amount'] !== '' && $pay['paid_amount'] !== null
            ? (float)$pay['paid_amount']
            : (float)$order['payable_amount'];
        $pay_method = trim((string)($pay['pay_method'] ?? ''));
        if ($pay_method === '') {
            $pay_method = (string)($order['pay_method'] ?? '');
        }
        $trade_no = trim((string)($pay['trade_no'] ?? ''));

        $stock_deducted = [];
        DB::beginTransaction();
        try {
            $pending_locks = DB::table('trade_new_order_stock_lock')
                ->where('order_type', $order_type)
                ->where('order_id', (int)$order['order_id'])
                ->where('handle_status', NewOrderStockLockService::HANDLE_PENDING)
                ->update([
                    'handle_status' => NewOrderStockLockService::HANDLE_DONE,
                    'handle_time' => $now,
                    'handle_remark' => '支付回调',
                ]);

            if (!$pending_locks) {
                $items = DB::table($item_table)
                    ->where('order_id', (int)$order['order_id'])
                    ->get();
                $item_rows = [];
                foreach ($items as $item) {
                    $item = (array)$item;
                    $item_rows[] = [
                        'product_id' => (int)($item['product_id'] ?? 0),
                        'product_name' => $item['product_name'] ?? '',
                        'quantity' => (int)($item['quantity'] ?? 0),
                    ];
                }
                $stock_deducted = $this->stockLockService->occupy(
                    $order_type,
                    (int)$order['order_id'],
                    $order_number,
                    $item_rows,
                    true
                );
            }

            $affected = DB::table($table)
                ->where('order_id', (int)$order['order_id'])
                ->where('order_status', $pending)
                ->update([
                    'order_status' => $paid_status,
                    'stock_locked' => 1,
                    'pay_time' => $pay_time,
                    'paid_amount' => $paid_amount,
                    'pay_method' => $pay_method,
                    'update_time' => $now,
                ]);
            if (!$affected) {
                throw new ErrorException('未更改到符合条件的订单');
            }

            $log = '支付成功，订单已支付';
            if ($trade_no !== '') {
                $log .= '，交易号：' . $trade_no;
            }
            DB::table($log_table)->insert([
                'order_id' => (int)$order['order_id'],
                'log_content' => $log,
                'operator_name' => '支付回调',
                'add_time' => $now,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($stock_deducted) {
                $this->newProductService->restoreOrderStock($stock_deducted);
            }
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '支付回调失败');
        }

        return true;
    }

    private function resolveOrderType($order_number)
    {
        if (stripos($order_number, 'PO') === 0) {
            return NewOrderStockLockService::TYPE_BUY;
        }
        if (stripos($order_number, 'RO') === 0) {
            return NewOrderStockLockService::TYPE_RENT;
        }
        throw new ErrorException('无法识别订单类型');
    }

    private function normalizeDateTime($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $ts = (int)$value;
            if ($ts >= 10000000000) {
                $ts = (int)floor($ts / 1000);
            }
            return date('Y-m-d H:i:s', $ts);
        }
        $ts = strtotime((string)$value);
        return $ts === false ? null : date('Y-m-d H:i:s', $ts);
    }
}
