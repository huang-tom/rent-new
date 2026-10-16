<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use App\Support\BindConnectCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Pt\Services\NewProductService;
use Modules\Sys\Services\ConfigBaseService;
use Yansongda\Pay\Pay;

/**
 * 新订单支付：微信小程序下单、微信回调、支付回调；order_id 传订单号，PO 购买、RO 租赁
 */
class NewOrderPayService
{
    const PAY_METHOD_WECHAT = '微信支付';

    private $stockLockService;
    private $newProductService;
    private $configBaseService;

    public function __construct(
        NewOrderStockLockService $stockLockService,
        NewProductService        $newProductService,
        ConfigBaseService        $configBaseService
    )
    {
        $this->stockLockService = $stockLockService;
        $this->newProductService = $newProductService;
        $this->configBaseService = $configBaseService;
    }

    /**
     * 微信小程序下单（JSAPI），返回 wx.requestPayment 所需参数
     * @param string $order_number 订单号
     * @param int $user_id 当前登录用户
     */
    public function wechatMiniPay($order_number, $user_id)
    {
        $user_id = (int)$user_id;
        if ($user_id <= 0) {
            throw new ErrorException('请先登录');
        }

        $order_number = trim((string)$order_number);
        if ($order_number === '') {
            throw new ErrorException('订单编号不能为空');
        }

        $order_type = $this->resolveOrderType($order_number);
        $order = $this->findOrder($order_type, $order_number);
        if ((int)$order['user_id'] !== $user_id) {
            throw new ErrorException('订单信息不存在');
        }

        $pending = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PENDING
            : NewOrderService::STATUS_PENDING;
        $paying = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PAYING
            : NewOrderService::STATUS_PAYING;
        if (!in_array((int)$order['order_status'], [$pending, $paying], true)) {
            throw new ErrorException('订单不是待支付或支付中状态');
        }

        $total = (int)round((float)$order['payable_amount'] * 100);
        if ($total <= 0) {
            throw new ErrorException('订单应付金额须大于0');
        }

        $openid = $this->getXcxOpenid($user_id);
        if ($openid === '') {
            throw new ErrorException('当前账号未绑定微信，请先使用微信登录');
        }

        $params = [
            'out_trade_no' => $order_number,
            'description' => $this->getPayDescription($order_type, (int)$order['order_id'], $order_number),
            'amount' => [
                'total' => $total,
                'currency' => 'CNY',
            ],
            'payer' => [
                'openid' => $openid,
            ],
        ];

        try {
            $result = Pay::wechat($this->getMiniPayConfig())->mini($params);
        } catch (\Throwable $e) {
            Log::error('新订单微信下单失败：' . $order_number . '，' . $e->getMessage());
            throw new ErrorException('微信下单失败：' . $e->getMessage());
        }

        if ((int)$order['order_status'] === $pending) {
            $this->markPaying($order_type, (int)$order['order_id'], $pending, $paying);
        }

        $data = $result->toArray();
        $data['order_number'] = $order_number;
        $data['payment_amount'] = round($total / 100, 2);

        return $data;
    }

    /**
     * 微信支付 V3 回调：验签解密后把订单置为已支付
     */
    public function wechatNotify()
    {
        $pay = Pay::wechat($this->getMiniPayConfig());

        try {
            $notify = $pay->callback()->toArray();
            Log::info('新订单微信支付回调', $notify);

            $data = $notify['resource']['ciphertext'] ?? [];
            if (($data['trade_state'] ?? '') !== 'SUCCESS') {
                return $pay->success();
            }

            $order_number = (string)($data['out_trade_no'] ?? '');
            $total = (int)($data['amount']['total'] ?? 0);
            $order = $this->findOrder($this->resolveOrderType($order_number), $order_number);
            if ($total !== (int)round((float)$order['payable_amount'] * 100)) {
                throw new ErrorException('支付金额与订单应付金额不一致');
            }

            $this->setPaidYes($order_number, [
                'paid_amount' => $total / 100,
                'pay_method' => self::PAY_METHOD_WECHAT,
                'pay_time' => $data['success_time'] ?? null,
                'trade_no' => $data['transaction_id'] ?? '',
            ]);

            return $pay->success();
        } catch (\Throwable $e) {
            Log::error('新订单微信支付回调失败：' . $e->getMessage());
            return response('fail', 500);
        }
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
        $paying = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PAYING
            : NewOrderService::STATUS_PAYING;
        $paid_status = $order_type === NewOrderStockLockService::TYPE_RENT
            ? NewRentOrderService::STATUS_PAID
            : NewOrderService::STATUS_PAID;
        $payable_statuses = [$pending, $paying];

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
        if (!in_array((int)$order['order_status'], $payable_statuses, true)) {
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
            //屏蔽库存
            /*$pending_locks = DB::table('trade_new_order_stock_lock')
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
            }*/

            $affected = DB::table($table)
                ->where('order_id', (int)$order['order_id'])
                ->whereIn('order_status', $payable_statuses)
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
                //屏蔽库存
                //$this->newProductService->restoreOrderStock($stock_deducted);
            }
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '支付回调失败');
        }

        return true;
    }

    /**
     * 微信下单成功后，待支付改为支付中
     */
    private function markPaying($order_type, $order_id, $pending, $paying)
    {
        $table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order'
            : 'trade_new_order';
        $log_table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order_log'
            : 'trade_new_order_log';
        $now = getDateTime();
        $affected = DB::table($table)
            ->where('order_id', (int)$order_id)
            ->where('order_status', $pending)
            ->update([
                'order_status' => $paying,
                'update_time' => $now,
            ]);
        if (!$affected) {
            return;
        }
        DB::table($log_table)->insert([
            'order_id' => (int)$order_id,
            'log_content' => '发起微信支付，订单支付中',
            'operator_name' => '系统',
            'add_time' => $now,
        ]);
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

    private function findOrder($order_type, $order_number)
    {
        $table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order'
            : 'trade_new_order';
        $order = DB::table($table)
            ->where('order_number', $order_number)
            ->where('is_deleted', 0)
            ->first();
        if (!$order) {
            throw new ErrorException('订单信息不存在');
        }
        return (array)$order;
    }

    private function getXcxOpenid($user_id)
    {
        $openid = DB::table('account_user_bind_connect')
            ->where('user_id', (int)$user_id)
            ->where('bind_type', BindConnectCode::WEIXIN_XCX)
            ->where('bind_active', 1)
            ->value('bind_openid');
        return trim((string)$openid);
    }

    /**
     * 商品描述：首个商品名，多件时追加「等N件」，微信限制 127 字节
     */
    private function getPayDescription($order_type, $order_id, $order_number)
    {
        $item_table = $order_type === NewOrderStockLockService::TYPE_RENT
            ? 'trade_new_rent_order_item'
            : 'trade_new_order_item';
        $names = DB::table($item_table)
            ->where('order_id', $order_id)
            ->orderBy('item_id')
            ->pluck('product_name')
            ->toArray();
        $names = array_values(array_filter(array_map('trim', $names), 'strlen'));
        if (!$names) {
            return '订单' . $order_number;
        }

        $description = $names[0];
        if (count($names) > 1) {
            $description .= '等' . count($names) . '件';
        }
        return mb_substr($description, 0, 40);
    }

    private function getMiniPayConfig()
    {
        $config = $this->configBaseService->getWechatConfig();
        $config['wechat']['default']['mini_app_id'] = ConfigBaseService::getConfig('wechat_xcx_app_id');
        $config['wechat']['default']['notify_url'] = env('URL_PC') . '/front/trade/callback/wechatNotify';
        $config['_force'] = true;
        return $config;
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
