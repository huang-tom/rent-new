<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Services\NewProductService;
use Modules\Sys\Services\NumberSeqService;
use Modules\Trade\Repositories\Contracts\NewRentOrderAddressRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderItemRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderLogRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderOpRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderRefundRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderRepairRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderRepository;
use Modules\Trade\Repositories\Contracts\NewRentOrderTagRepository;

/**
 * 新租赁订单（与旧订单 / 新购买订单隔离）
 */
class NewRentOrderService extends BaseService
{
    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_RENTING = 3;
    const STATUS_RETURNED = 4;
    const STATUS_BUYOUT = 5;
    const STATUS_REFUNDED = 6;
    const STATUS_CLOSED = 7;

    const OP_RETURN = 1;
    const OP_RENEW = 2;
    const OP_BUYOUT = 3;

    private $userInfoRepository;
    private $itemRepository;
    private $addressRepository;
    private $tagRepository;
    private $refundRepository;
    private $logRepository;
    private $opRepository;
    private $repairRepository;
    private $numberSeqService;
    private $newProductService;
    private $stockLockService;

    public function __construct(
        NewRentOrderRepository        $newRentOrderRepository,
        UserInfoRepository            $userInfoRepository,
        NewRentOrderItemRepository    $itemRepository,
        NewRentOrderAddressRepository $addressRepository,
        NewRentOrderTagRepository     $tagRepository,
        NewRentOrderRefundRepository  $refundRepository,
        NewRentOrderLogRepository     $logRepository,
        NewRentOrderOpRepository      $opRepository,
        NewRentOrderRepairRepository  $repairRepository,
        NumberSeqService              $numberSeqService,
        NewProductService             $newProductService,
        NewOrderStockLockService      $stockLockService
    )
    {
        $this->repository = $newRentOrderRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->itemRepository = $itemRepository;
        $this->addressRepository = $addressRepository;
        $this->tagRepository = $tagRepository;
        $this->refundRepository = $refundRepository;
        $this->logRepository = $logRepository;
        $this->opRepository = $opRepository;
        $this->repairRepository = $repairRepository;
        $this->numberSeqService = $numberSeqService;
        $this->newProductService = $newProductService;
        $this->stockLockService = $stockLockService;
    }

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
        $items = $this->itemRepository->find([['order_id', 'IN', $order_ids]]) ?: [];
        foreach ($items as $item) {
            $oid = (int)$item['order_id'];
            if (!isset($item_map[$oid])) {
                $item_map[$oid] = [];
            }
            $item_map[$oid][] = $item;
        }

        $tag_map = [];
        $tags = $this->tagRepository->find([['order_id', 'IN', $order_ids]]) ?: [];
        foreach ($tags as $tag) {
            $oid = (int)$tag['order_id'];
            if (!isset($tag_map[$oid])) {
                $tag_map[$oid] = [];
            }
            $tag_map[$oid][] = $tag;
        }

        foreach ($data['data'] as $k => $row) {
            $oid = (int)$row['order_id'];
            $user = $user_map[$row['user_id']] ?? [];
            $order_items = $item_map[$oid] ?? [];
            $first_item = $order_items ? reset($order_items) : [];

            $data['data'][$k]['order_status_text'] = $this->formatStatusText($row['order_status'] ?? 0);
            $data['data'][$k]['user_name'] = $user['user_nickname'] ?? '';
            $data['data'][$k]['user_mobile'] = $user['user_mobile'] ?? '';
            $data['data'][$k]['item_summary'] = $this->formatItemSummary($order_items);
            $data['data'][$k]['item_quantity'] = array_sum(array_column($order_items, 'quantity'));
            $data['data'][$k]['product_name'] = $first_item['product_name'] ?? '';
            $data['data'][$k]['spec_name'] = $first_item['spec_name'] ?? '';
            $data['data'][$k]['daily_rent_period_text'] = $this->formatDailyRentPeriod($row);
            $data['data'][$k]['tags'] = $tag_map[$oid] ?? [];
        }

        return $data;
    }

    public function getOrder($order_id)
    {
        $order = $this->getExistOrder($order_id);
        $order['order_status_text'] = $this->formatStatusText($order['order_status'] ?? 0);

        $user = [];
        if (!empty($order['user_id'])) {
            $user = $this->userInfoRepository->getOne($order['user_id']) ?: [];
        }

        $items = $this->itemRepository->find(['order_id' => $order_id], ['item_id' => 'ASC']) ?: [];
        $address = $this->addressRepository->find(['order_id' => $order_id]) ?: [];
        $address = $address ? reset($address) : null;
        $tags = $this->tagRepository->find(['order_id' => $order_id], ['tag_id' => 'ASC']) ?: [];
        $refunds = $this->refundRepository->find(['order_id' => $order_id], ['refund_id' => 'DESC']) ?: [];
        $logs = $this->logRepository->find(['order_id' => $order_id], ['log_id' => 'DESC']) ?: [];
        $ops = $this->opRepository->find(['order_id' => $order_id], ['op_id' => 'DESC']) ?: [];
        $repairs = $this->repairRepository->find(['order_id' => $order_id], ['repair_id' => 'DESC']) ?: [];

        return [
            'order' => $order,
            'user' => $user,
            'items' => array_values($items),
            'address' => $address,
            'tags' => array_values($tags),
            'refunds' => array_values($refunds),
            'ops' => array_values($ops),
            'repairs' => array_values($repairs),
            'logs' => array_values($logs),
            'timeline' => $this->buildTimeline($order),
        ];
    }

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
            $deposit_total = 0;
            $discount_total = 0;
            $rent_days_max = 0;
            $daily_rent_first = 0;
            $equipment_price = 0;
            $normalized_items = [];

            foreach ($items as $item) {
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $daily = (float)($item['daily_rent'] ?? 0);
                $days = max(1, (int)($item['rent_days'] ?? 0));
                $rent_subtotal = isset($item['rent_subtotal'])
                    ? (float)$item['rent_subtotal']
                    : round($daily * $days * $qty, 2);
                $deposit = (float)($item['deposit_amount'] ?? 0);
                $discount = (float)($item['discount_amount'] ?? 0);

                $product_amount += $rent_subtotal;
                $deposit_total += $deposit;
                $discount_total += $discount;
                if ($days > $rent_days_max) {
                    $rent_days_max = $days;
                }
                if ($daily_rent_first <= 0 && $daily > 0) {
                    $daily_rent_first = $daily;
                }
                $eq = (float)($item['equipment_original_price'] ?? 0);
                if ($eq > $equipment_price) {
                    $equipment_price = $eq;
                }

                $normalized_items[] = [
                    'product_id' => (int)($item['product_id'] ?? 0),
                    'product_name' => $item['product_name'] ?? '',
                    'product_number' => $item['product_number'] ?? '',
                    'product_image' => $item['product_image'] ?? '',
                    'spec_name' => $item['spec_name'] ?? '',
                    'daily_rent' => $daily,
                    'rent_days' => $days,
                    'rent_start_date' => $this->normalizeDate($item['rent_start_date'] ?? null),
                    'rent_end_date' => $this->normalizeDate($item['rent_end_date'] ?? null),
                    'deposit_amount' => $deposit,
                    'rent_subtotal' => $rent_subtotal,
                    'discount_amount' => $discount,
                    'quantity' => $qty,
                    'locked_stock_qty' => (int)($item['locked_stock_qty'] ?? $qty),
                    'device_asset_no' => $item['device_asset_no'] ?? '',
                    'serial_number' => $item['serial_number'] ?? '',
                    'equipment_original_price' => $eq,
                    'add_time' => $now,
                ];
            }

            if (array_key_exists('deposit_amount', $data)) {
                $deposit_total = (float)$data['deposit_amount'];
            }
            if (array_key_exists('discount_amount', $data)) {
                $discount_total = (float)$data['discount_amount'];
            }
            if (array_key_exists('rent_days', $data) && (int)$data['rent_days'] > 0) {
                $rent_days_max = (int)$data['rent_days'];
            }
            if (array_key_exists('daily_rent', $data) && (float)$data['daily_rent'] > 0) {
                $daily_rent_first = (float)$data['daily_rent'];
            }
            if (array_key_exists('equipment_original_price', $data)) {
                $equipment_price = (float)$data['equipment_original_price'];
            }

            $freight = (float)($data['freight_amount'] ?? 0);
            $payable = array_key_exists('payable_amount', $data)
                ? (float)$data['payable_amount']
                : round($product_amount - $discount_total + $freight + $deposit_total, 2);
            $order_status = (int)($data['order_status'] ?? self::STATUS_PAID);
            $paid = array_key_exists('paid_amount', $data)
                ? (float)$data['paid_amount']
                : ($order_status === self::STATUS_PENDING ? 0 : $payable);

            $pay_time = $this->normalizeDateTime($data['pay_time'] ?? null);
            if ($order_status === self::STATUS_PENDING) {
                $pay_time = null;
            } elseif ($pay_time === null && $order_status !== self::STATUS_REFUNDED && $order_status !== self::STATUS_CLOSED) {
                $pay_time = $now;
            }

            $order_row = [
                'order_number' => $this->numberSeqService->createNextSeq('RO'),
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
                'rent_days' => $rent_days_max,
                'daily_rent' => $daily_rent_first,
                'product_amount' => $product_amount,
                'freight_amount' => $freight,
                'deposit_amount' => $deposit_total,
                'discount_amount' => $discount_total,
                'payable_amount' => $payable,
                'paid_amount' => $paid,
                'points_num' => (int)($data['points_num'] ?? 0),
                'points_amount' => (float)($data['points_amount'] ?? 0),
                'pay_method' => $data['pay_method'] ?? '',
                'equipment_original_price' => $equipment_price,
                'buyout_price' => 0,
                'buyout_payable' => 0,
                'order_time' => $this->normalizeDateTime($data['order_time'] ?? null) ?: $now,
                'pay_time' => $pay_time,
                'assign_warehouse_time' => null,
                'ship_time' => $this->normalizeDateTime($data['ship_time'] ?? null),
                'renting_time' => $this->normalizeDateTime($data['renting_time'] ?? null),
                'return_time' => null,
                'buyout_time' => null,
                'stock_locked' => $order_status === self::STATUS_PAID ? 1 : 0,
                'is_deleted' => 0,
                'add_time' => $now,
                'update_time' => $now,
            ];

            if ($order_row['fulfill_warehouse_code'] !== '' || $order_row['fulfill_warehouse_name'] !== '') {
                $order_row['assign_warehouse_time'] = $now;
            }
            if ($order_status === self::STATUS_SHIPPED && empty($order_row['ship_time'])) {
                $order_row['ship_time'] = $now;
            }
            if ($order_status === self::STATUS_RENTING && empty($order_row['renting_time'])) {
                $order_row['renting_time'] = $now;
                if (empty($order_row['ship_time'])) {
                    $order_row['ship_time'] = $now;
                }
            }

            $result = $this->repository->add($order_row);
            if (!$result) {
                throw new ErrorException('订单创建失败');
            }
            $order_id = (int)$result->getKey();
            if (in_array($order_status, [self::STATUS_PENDING, self::STATUS_PAID], true)) {
                $stock_deducted = $this->stockLockService->occupy(
                    NewOrderStockLockService::TYPE_RENT,
                    $order_id,
                    $order_row['order_number'],
                    $normalized_items,
                    $order_status === self::STATUS_PAID
                );
            }

            foreach ($normalized_items as $row) {
                $row['order_id'] = $order_id;
                $this->itemRepository->add($row);
            }

            $this->saveAddress($order_id, $data['address'] ?? [], $now);
            $this->addLog(
                $order_id,
                '提交租赁订单(' . $rent_days_max . '天)',
                $data['operator_name'] ?? '系统',
                $now
            );

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

    public function addTag($order_id, $tag_name, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $tag_name = trim((string)$tag_name);
        if ($tag_name === '') {
            throw new ErrorException('标签不能为空');
        }

        $exist = $this->tagRepository->find([
            'order_id' => $order_id,
            'tag_name' => $tag_name,
        ]) ?: [];
        if ($exist) {
            throw new ErrorException('标签已存在');
        }

        $now = getDateTime();
        $row = $this->tagRepository->add([
            'order_id' => (int)$order_id,
            'tag_name' => $tag_name,
            'add_time' => $now,
        ]);
        $this->addLog($order_id, '添加标签：' . $tag_name, $operator_name ?: '客服', $now);
        return $row;
    }

    public function removeTag($order_id, $tag_id, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $tag = $this->tagRepository->getOne($tag_id);
        if (!$tag || (int)$tag['order_id'] !== (int)$order_id) {
            throw new ErrorException('标签不存在');
        }
        $result = $this->tagRepository->remove($tag_id);
        if (!$result) {
            throw new ErrorException('删除标签失败');
        }
        $this->addLog($order_id, '删除标签：' . ($tag['tag_name'] ?? ''), $operator_name ?: '客服', getDateTime());
        return true;
    }

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
            $this->refundRepository->add([
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

    /**
     * 退租 / 提前归还
     */
    public function returnRent($order_id, $return_date = null, $operator_name = '')
    {
        $order = $this->getExistOrder($order_id);
        $status = (int)$order['order_status'];
        if (!in_array($status, [self::STATUS_SHIPPED, self::STATUS_RENTING], true)) {
            throw new ErrorException('当前状态不可退租');
        }

        $items = $this->itemRepository->find(['order_id' => $order_id], ['item_id' => 'ASC']) ?: [];
        $items = array_values($items);
        if (!$items) {
            throw new ErrorException('订单无商品明细');
        }

        $item = $items[0];
        $rent_days = max(1, (int)($item['rent_days'] ?? $order['rent_days'] ?? 1));
        $daily = (float)($item['daily_rent'] ?? $order['daily_rent'] ?? 0);
        $deposit = (float)($order['deposit_amount'] ?? 0);
        $product_amount = (float)($order['product_amount'] ?? 0);
        $discount = (float)($order['discount_amount'] ?? 0);
        $effective_daily = $rent_days > 0
            ? round(max(0, $product_amount - $discount) / $rent_days, 2)
            : $daily;

        $start = $this->normalizeDate($item['rent_start_date'] ?? null)
            ?: $this->normalizeDate($order['order_time'] ?? null)
            ?: date('Y-m-d');
        $end = $this->normalizeDate($item['rent_end_date'] ?? null)
            ?: date('Y-m-d', strtotime($start . ' +' . ($rent_days - 1) . ' days'));

        $return_date = $this->normalizeDate($return_date) ?: date('Y-m-d');
        if ($return_date > $end) {
            throw new ErrorException('退租日期不能超过到期日');
        }
        if ($return_date < $start) {
            $return_date = $start;
        }

        $used_days = (int)floor((strtotime($return_date) - strtotime($start)) / 86400) + 1;
        if ($used_days < 1) {
            $used_days = 1;
        }
        if ($used_days > $rent_days) {
            $used_days = $rent_days;
        }
        $unused_days = $rent_days - $used_days;
        $rent_refund = round($unused_days * $effective_daily, 2);
        $deposit_refund = $deposit;
        $total_refund = round($rent_refund + $deposit_refund, 2);

        $now = getDateTime();
        DB::beginTransaction();
        try {
            $this->opRepository->add([
                'order_id' => (int)$order_id,
                'op_type' => self::OP_RETURN,
                'op_date' => $return_date,
                'days' => $used_days,
                'rent_refund' => $rent_refund,
                'deposit_refund' => $deposit_refund,
                'renew_rent' => 0,
                'buyout_price' => 0,
                'deposit_offset' => 0,
                'payable_amount' => $total_refund,
                'remark' => '已使用' . $used_days . '/' . $rent_days . '天',
                'operator_name' => $operator_name ?: '客服',
                'add_time' => $now,
            ]);
            $this->repository->edit($order_id, [
                'order_status' => self::STATUS_RETURNED,
                'return_time' => $now,
                'update_time' => $now,
            ]);
            $this->addLog(
                $order_id,
                '退租归还，应退 ¥' . number_format($total_refund, 2, '.', '')
                . '（租金' . number_format($rent_refund, 2, '.', '')
                . '+押金' . number_format($deposit_refund, 2, '.', '') . '）',
                $operator_name ?: '客服',
                $now
            );
            DB::commit();
            return [
                'used_days' => $used_days,
                'rent_days' => $rent_days,
                'effective_daily' => $effective_daily,
                'rent_refund' => $rent_refund,
                'deposit_refund' => $deposit_refund,
                'total_refund' => $total_refund,
                'return_date' => $return_date,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '退租失败');
        }
    }

    /**
     * 续租
     */
    public function renew($order_id, $renew_days, $operator_name = '')
    {
        $order = $this->getExistOrder($order_id);
        $status = (int)$order['order_status'];
        if (!in_array($status, [self::STATUS_SHIPPED, self::STATUS_RENTING], true)) {
            throw new ErrorException('当前状态不可续租');
        }
        $renew_days = (int)$renew_days;
        if ($renew_days <= 0) {
            throw new ErrorException('续租天数须大于0');
        }

        $items = $this->itemRepository->find(['order_id' => $order_id], ['item_id' => 'ASC']) ?: [];
        $items = array_values($items);
        if (!$items) {
            throw new ErrorException('订单无商品明细');
        }

        $daily = (float)($order['daily_rent'] ?? 0);
        if ($daily <= 0) {
            $daily = (float)($items[0]['daily_rent'] ?? 0);
        }
        $renew_rent = round($daily * $renew_days, 2);
        $new_rent_days = (int)($order['rent_days'] ?? 0) + $renew_days;

        $now = getDateTime();
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $end = $this->normalizeDate($item['rent_end_date'] ?? null);
                $new_end = $end
                    ? date('Y-m-d', strtotime($end . ' +' . $renew_days . ' days'))
                    : null;
                $this->itemRepository->edit($item['item_id'], [
                    'rent_days' => (int)($item['rent_days'] ?? 0) + $renew_days,
                    'rent_end_date' => $new_end,
                    'rent_subtotal' => round((float)($item['rent_subtotal'] ?? 0) + $renew_rent, 2),
                ]);
            }

            $expect_return = $this->normalizeDateTime($order['expect_return_time'] ?? null);
            $new_expect = $expect_return
                ? date('Y-m-d H:i:s', strtotime($expect_return . ' +' . $renew_days . ' days'))
                : null;

            $this->repository->edit($order_id, [
                'rent_days' => $new_rent_days,
                'product_amount' => round((float)$order['product_amount'] + $renew_rent, 2),
                'payable_amount' => round((float)$order['payable_amount'] + $renew_rent, 2),
                'paid_amount' => round((float)$order['paid_amount'] + $renew_rent, 2),
                'expect_return_time' => $new_expect,
                'update_time' => $now,
            ]);

            $this->opRepository->add([
                'order_id' => (int)$order_id,
                'op_type' => self::OP_RENEW,
                'op_date' => date('Y-m-d'),
                'days' => $renew_days,
                'rent_refund' => 0,
                'deposit_refund' => 0,
                'renew_rent' => $renew_rent,
                'buyout_price' => 0,
                'deposit_offset' => 0,
                'payable_amount' => $renew_rent,
                'remark' => '续租后合计租期' . $new_rent_days . '天',
                'operator_name' => $operator_name ?: '客服',
                'add_time' => $now,
            ]);
            $this->addLog(
                $order_id,
                '续租 +' . $renew_days . '天，续租租金 ¥' . number_format($renew_rent, 2, '.', ''),
                $operator_name ?: '客服',
                $now
            );
            DB::commit();
            return [
                'renew_days' => $renew_days,
                'renew_rent' => $renew_rent,
                'rent_days' => $new_rent_days,
                'daily_rent' => $daily,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '续租失败');
        }
    }

    /**
     * 买断
     */
    public function buyout($order_id, $buyout_price = null, $operator_name = '')
    {
        $order = $this->getExistOrder($order_id);
        $status = (int)$order['order_status'];
        if (!in_array($status, [self::STATUS_SHIPPED, self::STATUS_RENTING], true)) {
            throw new ErrorException('当前状态不可买断');
        }

        if ($buyout_price === null || $buyout_price === '') {
            $buyout_price = (float)($order['equipment_original_price'] ?? 0);
        } else {
            $buyout_price = (float)$buyout_price;
        }
        if ($buyout_price < 0) {
            throw new ErrorException('买断价不能为负');
        }

        $deposit = (float)($order['deposit_amount'] ?? 0);
        $buyout_payable = max(0, round($buyout_price - $deposit, 2));

        $now = getDateTime();
        DB::beginTransaction();
        try {
            $this->opRepository->add([
                'order_id' => (int)$order_id,
                'op_type' => self::OP_BUYOUT,
                'op_date' => date('Y-m-d'),
                'days' => 0,
                'rent_refund' => 0,
                'deposit_refund' => 0,
                'renew_rent' => 0,
                'buyout_price' => $buyout_price,
                'deposit_offset' => $deposit,
                'payable_amount' => $buyout_payable,
                'remark' => '押金抵扣买断款，设备归用户',
                'operator_name' => $operator_name ?: '客服',
                'add_time' => $now,
            ]);
            $this->repository->edit($order_id, [
                'order_status' => self::STATUS_BUYOUT,
                'buyout_price' => $buyout_price,
                'buyout_payable' => $buyout_payable,
                'buyout_time' => $now,
                'update_time' => $now,
            ]);
            $this->addLog(
                $order_id,
                '买断，买断价 ¥' . number_format($buyout_price, 2, '.', '')
                . '，押金抵扣 ¥' . number_format($deposit, 2, '.', '')
                . '，应付 ¥' . number_format($buyout_payable, 2, '.', ''),
                $operator_name ?: '客服',
                $now
            );
            DB::commit();
            return [
                'buyout_price' => $buyout_price,
                'deposit_offset' => $deposit,
                'buyout_payable' => $buyout_payable,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '买断失败');
        }
    }

    /**
     * 报修
     */
    public function repair($order_id, $fault_type, $fault_desc, $handle_method, $operator_name = '')
    {
        $this->getExistOrder($order_id);
        $fault_type = trim((string)$fault_type);
        $fault_desc = trim((string)$fault_desc);
        $handle_method = trim((string)$handle_method);
        if ($fault_type === '') {
            throw new ErrorException('请选择故障类型');
        }

        $now = getDateTime();
        $row = $this->repairRepository->add([
            'order_id' => (int)$order_id,
            'fault_type' => $fault_type,
            'fault_desc' => $fault_desc,
            'handle_method' => $handle_method,
            'operator_name' => $operator_name ?: '客服',
            'add_time' => $now,
        ]);
        $this->addLog(
            $order_id,
            '报修：' . $fault_type . ($handle_method !== '' ? '，处理方式：' . $handle_method : ''),
            $operator_name ?: '客服',
            $now
        );
        return $row;
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

        $this->addressRepository->add([
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
        $this->logRepository->add([
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

    private function normalizeDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $dt = $this->normalizeDateTime($value);
        return $dt ? substr($dt, 0, 10) : null;
    }

    private function formatStatusText($status)
    {
        $map = [
            self::STATUS_PENDING => '待支付',
            self::STATUS_PAID => '已支付',
            self::STATUS_SHIPPED => '已发货',
            self::STATUS_RENTING => '租赁中',
            self::STATUS_RETURNED => '已归还',
            self::STATUS_BUYOUT => '已买断',
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

    private function formatDailyRentPeriod(array $row)
    {
        $daily = (float)($row['daily_rent'] ?? 0);
        $days = (int)($row['rent_days'] ?? 0);
        if ($daily <= 0 && $days <= 0) {
            return '';
        }
        return '¥' . rtrim(rtrim(number_format($daily, 2, '.', ''), '0'), '.') . ' × ' . $days . '天';
    }

    private function buildTimeline(array $order)
    {
        $nodes = [
            ['key' => 'submit', 'title' => '提交订单', 'time' => $order['order_time'] ?? null],
            ['key' => 'pay', 'title' => '支付(含押金)', 'time' => $order['pay_time'] ?? null],
            ['key' => 'warehouse', 'title' => '仓库已分配', 'time' => $order['assign_warehouse_time'] ?? null],
            ['key' => 'ship', 'title' => '商家发货', 'time' => $order['ship_time'] ?? null],
            ['key' => 'renting', 'title' => '租赁中', 'time' => $order['renting_time'] ?? null],
            ['key' => 'settle', 'title' => '归还结算', 'time' => $order['return_time'] ?? ($order['buyout_time'] ?? null)],
        ];

        $status = (int)($order['order_status'] ?? 0);
        $current = 'submit';
        if (!empty($order['pay_time'])) {
            $current = 'pay';
        }
        if (!empty($order['assign_warehouse_time'])) {
            $current = 'warehouse';
        }
        if (!empty($order['ship_time']) || $status === self::STATUS_SHIPPED) {
            $current = 'ship';
        }
        if (!empty($order['renting_time']) || $status === self::STATUS_RENTING) {
            $current = 'renting';
        }
        if ($status === self::STATUS_RETURNED || $status === self::STATUS_BUYOUT) {
            $current = 'settle';
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
