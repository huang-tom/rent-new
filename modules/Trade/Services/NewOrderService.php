<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Services\NewProductService;
use Modules\Sys\Services\NumberSeqService;
use Modules\Trade\Repositories\Contracts\NewOrderAddressRepository;
use Modules\Trade\Repositories\Contracts\NewOrderItemRepository;
use Modules\Trade\Repositories\Contracts\NewOrderLogRepository;
use Modules\Trade\Repositories\Contracts\NewOrderRefundRepository;
use Modules\Trade\Repositories\Contracts\NewOrderRepository;
use Modules\Trade\Repositories\Contracts\NewOrderTagRepository;

/**
 * 新购买订单（与旧订单隔离；租赁另表）
 */
class NewOrderService extends BaseService
{
    /** 待支付 */
    const STATUS_PENDING = 0;
    /** 已支付 */
    const STATUS_PAID = 1;
    /** 已发货 */
    const STATUS_SHIPPED = 2;
    /** 已完成 */
    const STATUS_COMPLETED = 3;
    /** 已退款 */
    const STATUS_REFUNDED = 4;
    /** 已关闭 */
    const STATUS_CLOSED = 5;

    /** 购买订单 */
    const ORDER_TYPE_BUY = 1;

    private $userInfoRepository;
    private $newOrderItemRepository;
    private $newOrderAddressRepository;
    private $newOrderTagRepository;
    private $newOrderRefundRepository;
    private $newOrderLogRepository;
    private $numberSeqService;
    private $newProductService;
    private $stockLockService;

    public function __construct(
        NewOrderRepository        $newOrderRepository,
        UserInfoRepository        $userInfoRepository,
        NewOrderItemRepository    $newOrderItemRepository,
        NewOrderAddressRepository $newOrderAddressRepository,
        NewOrderTagRepository     $newOrderTagRepository,
        NewOrderRefundRepository  $newOrderRefundRepository,
        NewOrderLogRepository     $newOrderLogRepository,
        NumberSeqService          $numberSeqService,
        NewProductService         $newProductService,
        NewOrderStockLockService  $stockLockService
    )
    {
        $this->repository = $newOrderRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->newOrderItemRepository = $newOrderItemRepository;
        $this->newOrderAddressRepository = $newOrderAddressRepository;
        $this->newOrderTagRepository = $newOrderTagRepository;
        $this->newOrderRefundRepository = $newOrderRefundRepository;
        $this->newOrderLogRepository = $newOrderLogRepository;
        $this->numberSeqService = $numberSeqService;
        $this->newProductService = $newProductService;
        $this->stockLockService = $stockLockService;
    }

    /**
     * 列表
     */
    public function list($request, $criteria)
    {
        $data = parent::list($request, $criteria);
        if (empty($data['data'])) {
            return $data;
        }

        $order_ids = array_column($data['data'], 'order_id');
        $user_ids = array_unique(array_filter(array_column($data['data'], 'user_id')));

        $user_map = [];
        if ($user_ids) {
            $users = $this->userInfoRepository->gets($user_ids) ?: [];
            foreach ($users as $user) {
                $user_map[$user['user_id']] = $user;
            }
        }

        $item_map = [];
        $items = $this->newOrderItemRepository->find([['order_id', 'IN', $order_ids]]) ?: [];
        foreach ($items as $item) {
            $oid = (int)$item['order_id'];
            if (!isset($item_map[$oid])) {
                $item_map[$oid] = [];
            }
            $item_map[$oid][] = $item;
        }

        $tag_map = [];
        $tags = $this->newOrderTagRepository->find([['order_id', 'IN', $order_ids]]) ?: [];
        foreach ($tags as $tag) {
            $oid = (int)$tag['order_id'];
            if (!isset($tag_map[$oid])) {
                $tag_map[$oid] = [];
            }
            $tag_map[$oid][] = $tag;
        }

        foreach ($data['data'] as $k => $row) {
            $oid = (int)$row['order_id'];
            $user = $user_map[$row['user_id']] ?? null;
            $order_items = $item_map[$oid] ?? [];

            $data['data'][$k]['order_status_text'] = $this->formatStatusText($row['order_status'] ?? 0);
            $data['data'][$k]['user_name'] = $user['user_nickname'] ?? '';
            $data['data'][$k]['user_mobile'] = $user['user_mobile'] ?? '';
            $data['data'][$k]['item_summary'] = $this->formatItemSummary($order_items);
            $data['data'][$k]['item_quantity'] = array_sum(array_column($order_items, 'quantity'));
            $data['data'][$k]['tags'] = $tag_map[$oid] ?? [];
        }

        return $data;
    }

    /**
     * 详情
     */
    public function getOrder($order_id)
    {
        $order = $this->getExistOrder($order_id);
        $order['order_status_text'] = $this->formatStatusText($order['order_status'] ?? 0);

        $user = [];
        if (!empty($order['user_id'])) {
            $user = $this->userInfoRepository->getOne($order['user_id']) ?: [];
        }

        $items = $this->newOrderItemRepository->find(['order_id' => $order_id], ['item_id' => 'ASC']) ?: [];
        $address = $this->newOrderAddressRepository->find(['order_id' => $order_id]) ?: [];
        $address = $address ? reset($address) : null;
        $tags = $this->newOrderTagRepository->find(['order_id' => $order_id], ['tag_id' => 'ASC']) ?: [];
        $refunds = $this->newOrderRefundRepository->find(['order_id' => $order_id], ['refund_id' => 'DESC']) ?: [];
        $logs = $this->newOrderLogRepository->find(['order_id' => $order_id], ['log_id' => 'DESC']) ?: [];

        return [
            'order' => $order,
            'user' => $user,
            'items' => array_values($items),
            'address' => $address,
            'tags' => array_values($tags),
            'refunds' => array_values($refunds),
            'logs' => array_values($logs),
            'timeline' => $this->buildTimeline($order),
        ];
    }

    /**
     * 新增购买订单
     */
    public function addOrder(array $data)
    {
        $items = $data['items'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }
        if (empty($items) || !is_array($items)) {
            throw new ErrorException('请填写商品明细');
        }

        $now = getDateTime();
        $stock_deducted = [];
        DB::beginTransaction();
        try {
            $user_id = (int)User::getUserId();

            $product_amount = 0;
            $normalized_items = [];
            foreach ($items as $item) {
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $price = (float)($item['unit_price'] ?? 0);
                $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : round($price * $qty, 2);
                $product_amount += $subtotal;
                $normalized_items[] = [
                    'product_id' => (int)($item['product_id'] ?? 0),
                    'product_name' => $item['product_name'] ?? '',
                    'product_number' => $item['product_number'] ?? '',
                    'product_image' => $item['product_image'] ?? '',
                    'spec_name' => $item['spec_name'] ?? '',
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'stock_out_qty' => (int)($item['stock_out_qty'] ?? 0),
                    'add_time' => $now,
                ];
            }

            $freight = (float)($data['freight_amount'] ?? 0);
            $deposit = (float)($data['deposit_amount'] ?? 0);
            $payable = array_key_exists('payable_amount', $data)
                ? (float)$data['payable_amount']
                : round($product_amount + $freight + $deposit, 2);
            $order_status = (int)($data['order_status'] ?? self::STATUS_PAID);
            if (!in_array($order_status, [self::STATUS_PENDING, self::STATUS_PAID], true)) {
                throw new ErrorException('新建订单状态只能是待支付或已支付');
            }

            $paid = array_key_exists('paid_amount', $data)
                ? (float)$data['paid_amount']
                : ($order_status === self::STATUS_PAID ? $payable : 0);

            $pay_time = null;
            if ($order_status === self::STATUS_PAID) {
                $pay_time = $this->normalizeDateTime($data['pay_time'] ?? null) ?: $now;
            }
            $order_row = [
                'order_number' => $this->numberSeqService->createNextSeq('PO'),
                'order_type' => self::ORDER_TYPE_BUY,
                'order_source' => $data['order_source'] ?? '',
                'order_store_name' => $data['order_store_name'] ?? '',
                'fulfill_warehouse_code' => $data['fulfill_warehouse_code'] ?? '',
                'fulfill_warehouse_name' => $data['fulfill_warehouse_name'] ?? '',
                'user_id' => $user_id,
                'order_status' => $order_status,
                'delivery_type' => $data['delivery_type'] ?? '',
                'express_no' => $data['express_no'] ?? '',
                'buyer_message' => $data['buyer_message'] ?? '',
                'service_remark' => $data['service_remark'] ?? '',
                'expect_delivery_time' => $this->normalizeDateTime($data['expect_delivery_time'] ?? null),
                'expect_return_time' => $this->normalizeDateTime($data['expect_return_time'] ?? null),
                'product_amount' => $product_amount,
                'freight_amount' => $freight,
                'deposit_amount' => $deposit,
                'payable_amount' => $payable,
                'paid_amount' => $paid,
                'points_num' => (int)($data['points_num'] ?? 0),
                'points_amount' => (float)($data['points_amount'] ?? 0),
                'pay_method' => $data['pay_method'] ?? '',
                'order_time' => $this->normalizeDateTime($data['order_time'] ?? null) ?: $now,
                'pay_time' => $pay_time,
                'assign_warehouse_time' => null,
                'stock_locked' => $order_status === self::STATUS_PAID ? 1 : 0,
                'is_deleted' => 0,
                'add_time' => $now,
                'update_time' => $now,
            ];

            if ($order_row['fulfill_warehouse_code'] !== '' || $order_row['fulfill_warehouse_name'] !== '') {
                $order_row['assign_warehouse_time'] = $now;
            }

            $result = $this->repository->add($order_row);
            if (!$result) {
                throw new ErrorException('订单创建失败');
            }
            $order_id = (int)$result->getKey();
            $stock_deducted = $this->stockLockService->occupy(
                NewOrderStockLockService::TYPE_BUY,
                $order_id,
                $order_row['order_number'],
                $normalized_items,
                $order_status === self::STATUS_PAID
            );

            foreach ($normalized_items as $row) {
                $row['order_id'] = $order_id;
                $this->newOrderItemRepository->add($row);
            }

            $this->saveAddress($order_id, $data['address'] ?? [], $now);
            $this->addLog($order_id, '创建购买订单', $data['operator_name'] ?? '系统', $now);

            DB::commit();
            return ['order_id' => $order_id, 'order_number' => $order_row['order_number']];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($stock_deducted) {
                $this->newProductService->restoreOrderStock($stock_deducted);
            }
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '订单创建失败');
        }
    }

    /**
     * 分仓：仅保存仓编码+名称
     */
    public function assignWarehouse($order_id, $warehouse_code, $warehouse_name, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $warehouse_code = trim((string)$warehouse_code);
        $warehouse_name = trim((string)$warehouse_name);
        if ($warehouse_code === '' && $warehouse_name === '') {
            throw new ErrorException('请填写分仓编码或仓库名称');
        }
        $now = getDateTime();
        $this->repository->edit($order_id, [
            'fulfill_warehouse_code' => $warehouse_code,
            'fulfill_warehouse_name' => $warehouse_name,
            'assign_warehouse_time' => $now,
            'update_time' => $now,
        ]);
        $this->addLog($order_id, '分配仓库：' . $warehouse_name . '(' . $warehouse_code . ')', $operator_name ?: '客服', $now);
        return true;
    }

    /**
     * 打标（人工输入标签名，仅绑当前订单）
     */
    public function addTag($order_id, $tag_name, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $tag_name = trim((string)$tag_name);
        if ($tag_name === '') {
            throw new ErrorException('标签不能为空');
        }

        $exist = $this->newOrderTagRepository->find([
            'order_id' => $order_id,
            'tag_name' => $tag_name,
        ]) ?: [];
        if ($exist) {
            throw new ErrorException('标签已存在');
        }

        $now = getDateTime();
        $row = $this->newOrderTagRepository->add([
            'order_id' => (int)$order_id,
            'tag_name' => $tag_name,
            'add_time' => $now,
        ]);
        $this->addLog($order_id, '添加标签：' . $tag_name, $operator_name ?: '客服', $now);
        return $row;
    }

    /**
     * 删除订单标签
     */
    public function removeTag($order_id, $tag_id, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $tag = $this->newOrderTagRepository->getOne($tag_id);
        if (!$tag || (int)$tag['order_id'] !== (int)$order_id) {
            throw new ErrorException('标签不存在');
        }
        $result = $this->newOrderTagRepository->remove($tag_id);
        if (!$result) {
            throw new ErrorException('删除标签失败');
        }
        $this->addLog($order_id, '删除标签：' . ($tag['tag_name'] ?? ''), $operator_name ?: '客服', getDateTime());
        return true;
    }

    /**
     * 更新客服备注（订单主表字段）
     */
    public function addRemark($order_id, $remark_content, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $remark_content = trim((string)$remark_content);
        if ($remark_content === '') {
            throw new ErrorException('备注不能为空');
        }
        $now = getDateTime();
        $this->repository->edit($order_id, [
            'service_remark' => $remark_content,
            'update_time' => $now,
        ]);
        $this->addLog($order_id, '客服备注：' . $remark_content, $operator_name ?: '客服', $now);
        return true;
    }

    /**
     * 退款：记录金额+原因，状态改为已退款
     */
    public function refund($order_id, $refund_amount, $refund_reason, $operator_name = '')
    {
        $order = $this->getExistOrder($order_id);
        if ((int)$order['order_status'] === self::STATUS_REFUNDED) {
            throw new ErrorException('订单已退款');
        }
        $refund_amount = (float)$refund_amount;
        if ($refund_amount <= 0) {
            throw new ErrorException('退款金额须大于0');
        }
        $refund_reason = trim((string)$refund_reason);
        if ($refund_reason === '') {
            throw new ErrorException('请填写退款原因');
        }

        $now = getDateTime();
        DB::beginTransaction();
        try {
            $this->newOrderRefundRepository->add([
                'order_id' => (int)$order_id,
                'refund_amount' => $refund_amount,
                'refund_reason' => $refund_reason,
                'operator_name' => $operator_name ?: '客服',
                'add_time' => $now,
            ]);
            $this->repository->edit($order_id, [
                'order_status' => self::STATUS_REFUNDED,
                'update_time' => $now,
            ]);
            $this->addLog(
                $order_id,
                '退款 ¥' . number_format($refund_amount, 2, '.', '') . '，原因：' . $refund_reason,
                $operator_name ?: '客服',
                $now
            );
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '退款失败');
        }
    }

    private function saveAddress($order_id, $address, $now)
    {
        if (is_string($address)) {
            $address = json_decode($address, true) ?: [];
        }
        if (empty($address) || !is_array($address)) {
            return;
        }

        $full = $address['address_full'] ?? '';
        if ($full === '') {
            $full = trim(($address['province'] ?? '') . ($address['city'] ?? '') . ($address['district'] ?? '') . ($address['address_detail'] ?? ''));
        }

        $this->newOrderAddressRepository->add([
            'order_id' => (int)$order_id,
            'consignee' => $address['consignee'] ?? '',
            'consignee_mobile' => $address['consignee_mobile'] ?? '',
            'province' => $address['province'] ?? '',
            'city' => $address['city'] ?? '',
            'district' => $address['district'] ?? '',
            'address_detail' => $address['address_detail'] ?? '',
            'address_full' => $full,
            'add_time' => $now,
        ]);
    }

    private function addLog($order_id, $content, $operator_name, $now = null)
    {
        $this->newOrderLogRepository->add([
            'order_id' => (int)$order_id,
            'log_content' => $content,
            'operator_name' => $operator_name,
            'add_time' => $now ?: getDateTime(),
        ]);
    }

    private function getExistOrder($order_id)
    {
        $order = $this->repository->getOne($order_id);
        if (!$order || !empty($order['is_deleted'])) {
            throw new ErrorException('订单不存在');
        }
        return $order;
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

    private function formatStatusText($status)
    {
        $map = [
            self::STATUS_PENDING => '待支付',
            self::STATUS_PAID => '已支付',
            self::STATUS_SHIPPED => '已发货',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_REFUNDED => '已退款',
            self::STATUS_CLOSED => '已关闭',
        ];
        return $map[(int)$status] ?? '';
    }

    private function formatItemSummary(array $items)
    {
        if (!$items) {
            return '';
        }
        $parts = [];
        foreach ($items as $item) {
            $parts[] = ($item['product_name'] ?? '') . ' x ' . (int)($item['quantity'] ?? 0);
        }
        return implode('；', $parts);
    }

    private function buildTimeline(array $order)
    {
        $nodes = [
            ['key' => 'submit', 'title' => '提交订单', 'time' => $order['order_time'] ?? null],
            ['key' => 'pay', 'title' => '支付完成', 'time' => $order['pay_time'] ?? null],
            ['key' => 'warehouse', 'title' => '仓库已分配', 'time' => $order['assign_warehouse_time'] ?? null],
            ['key' => 'ship', 'title' => '商家发货', 'time' => null],
            ['key' => 'receive', 'title' => '确认收货', 'time' => null],
            ['key' => 'done', 'title' => '订单完成', 'time' => null],
        ];

        $status = (int)($order['order_status'] ?? 0);
        if ($status === self::STATUS_SHIPPED || $status === self::STATUS_COMPLETED) {
            $nodes[3]['time'] = $order['update_time'] ?? null;
        }
        if ($status === self::STATUS_COMPLETED) {
            $nodes[4]['time'] = $order['update_time'] ?? null;
            $nodes[5]['time'] = $order['update_time'] ?? null;
        }

        $current = 'submit';
        if (!empty($order['pay_time'])) {
            $current = 'pay';
        }
        if (!empty($order['assign_warehouse_time'])) {
            $current = 'warehouse';
        }
        if ($status === self::STATUS_SHIPPED) {
            $current = 'ship';
        }
        if ($status === self::STATUS_COMPLETED) {
            $current = 'done';
        }
        if ($status === self::STATUS_REFUNDED) {
            $current = 'pay';
        }

        foreach ($nodes as $i => $node) {
            $nodes[$i]['done'] = !empty($node['time']);
            $nodes[$i]['current'] = ($node['key'] === $current);
        }

        return $nodes;
    }
}
