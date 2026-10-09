<?php

namespace Modules\Trade\Services;

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Services\ConsumeReturnService;
use Modules\Shop\Repositories\Contracts\StoreShippingAddressRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Services\NumberSeqService;
use Modules\Trade\Repositories\Contracts\OrderDataRepository;
use Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;
use Modules\Trade\Repositories\Contracts\OrderItemRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnItemRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnReasonRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnRepository;
use Modules\Trade\Repositories\Models\OrderBase;
use Modules\Trade\Repositories\Models\OrderReturn;
use App\Exceptions\ErrorException;

/**
 * Class OrderReturnService.
 *
 * @package Modules\Trade\Services
 */
class OrderReturnService extends BaseService
{
    private $configBaseRepository;
    private $orderReturnItemRepository;
    private $orderInfoRepository;
    private $orderDataRepository;
    private $orderItemRepository;
    private $orderReturnReasonRepository;
    private $orderDeliveryAddressRepository;
    private $storeShippingAddressRepository;

    private $numberSeqService;
    private $consumeReturnService;

    public function __construct(
        ConfigBaseRepository           $configBaseRepository,
        OrderReturnRepository          $orderReturnRepository,
        OrderReturnItemRepository      $orderReturnItemRepository,
        OrderInfoRepository            $orderInfoRepository,
        OrderDataRepository            $orderDataRepository,
        OrderItemRepository            $orderItemRepository,
        OrderReturnReasonRepository    $orderReturnReasonRepository,
        OrderDeliveryAddressRepository $orderDeliveryAddressRepository,
        StoreShippingAddressRepository $storeShippingAddressRepository,

        NumberSeqService               $numberSeqService,
        ConsumeReturnService           $consumeReturnService
    )
    {
        $this->configBaseRepository = $configBaseRepository;
        $this->repository = $orderReturnRepository;
        $this->orderReturnItemRepository = $orderReturnItemRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->orderDataRepository = $orderDataRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderReturnReasonRepository = $orderReturnReasonRepository;
        $this->orderDeliveryAddressRepository = $orderDeliveryAddressRepository;
        $this->storeShippingAddressRepository = $storeShippingAddressRepository;

        $this->numberSeqService = $numberSeqService;
        $this->consumeReturnService = $consumeReturnService;
    }


    /**
     * 获取列表
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        if (!empty($data['data'])) {
            $items = $this->getReturnItems($data['data']);
            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 获取退款商品
     * @param $items
     * @return mixed
     */
    public function getReturnItems($items)
    {
        $return_ids = array_column($items, 'return_id');
        $return_items = $this->orderReturnItemRepository->find([['return_id', 'IN', $return_ids]]);

        if (!empty($return_items)) {
            $order_item_ids = array_column_unique($return_items, 'order_item_id');
            $order_items = $this->orderItemRepository->gets($order_item_ids);
            foreach ($return_items as $k => $return_item) {
                if (isset($order_items[$return_item['order_item_id']])) {
                    $order_item = $order_items[$return_item['order_item_id']];
                    $return_items[$k]['order_item_quantity'] = $order_item['order_item_quantity'];
                    $return_items[$k]['order_item_image'] = $order_item['order_item_image'];
                    $return_items[$k]['item_name'] = $order_item['item_name'];
                    $return_items[$k]['item_id'] = $order_item['item_id'];
                    $return_items[$k]['product_id'] = $order_item['product_id'];
                    $return_items[$k]['product_name'] = $order_item['product_name'];
                    $return_items[$k]['product_item_name'] = $order_item['product_name'] . ' ' . $order_item['item_name'];
                    $return_items[$k]['item_unit_price'] = $order_item['item_unit_price'];
                }
            }

            $return_items = arrayMap($return_items, 'return_id');
        }

        foreach ($items as $k => $item) {
            $items[$k]['items'] = [];
            if (isset($return_items[$item['return_id']])) {
                $items[$k]['items'] = $return_items[$item['return_id']];
            }

            $items[$k]['submit_return_refund_amount'] = array_sum(array_column($items[$k]['items'], 'return_item_subtotal'));
            $items[$k]['return_num'] = array_sum(array_column($items[$k]['items'], 'return_item_num'));
        }

        return $items;
    }


    /**
     * @param $order_id
     * @param $order_item_id
     * @param $user_id
     * @return array
     * @throws ErrorException
     */
    public function returnItem($order_id, $order_item_id, $user_id)
    {
        // 订单信息
        $order_info = $this->orderInfoRepository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__('订单信息有误!'));
        }
        if ($order_info['user_id'] !== $user_id) {
            throw new ErrorException(__('订单信息有误!'));
        }

        $order_item_row = $this->orderItemRepository->getOne($order_item_id);
        if ($order_item_row['order_id'] != $order_info['order_id']) {
            throw new ErrorException(__('订单信息有误!'));
        }

        $return_item_res = $order_item_row;
        $order_item_can_refund_amount = $order_item_row['order_item_payment_amount'];
        $return_item_res['can_refund_amount'] = $order_item_can_refund_amount - $order_item_row['order_item_return_subtotal'];
        $return_item_res['can_refund_num'] = $order_item_row['order_item_quantity'] - $order_item_row['order_item_return_num'];

        // 退货原因
        $order_return_reasons = $this->orderReturnReasonRepository->find([], ['return_reason_sort' => 'ASC']);
        if (!empty($order_return_reasons)) {
            $return_item_res['return_reason_list'] = array_values($order_return_reasons);
        }

        return $return_item_res;
    }


    /**
     * 用户申请退款
     * @param $user_id
     * @param $req
     * @return array
     * @throws ErrorException
     */
    public function addReturn($user_id, $req)
    {
        $order_id = $req['order_id'];
        $order_item_id = $req['order_item_id'];
        $return_refund_amount = $req['return_refund_amount'];
        $return_item_num = $req['return_item_num'];
        $return_buyer_message = $req['return_buyer_message'];
        $return_item_image = $req['return_item_image'];
        $refund_type = isset($req['refund_type']) ? $req['refund_type'] : 2;
        $return_tel = $req['return_tel'];
        $return_reason_id = $req['return_reason_id'];
        $return_flag = StateCode::ORDER_NOT_NEED_RETURN_GOODS;
        $return_state_id = StateCode::RETURN_PROCESS_CHECK;

        //todo 判断此订单商品是否有正在审核的退款单
        $order_return_rows = $this->repository->find([
            'order_id' => $order_id,
            'return_state_id' => StateCode::RETURN_PROCESS_CHECK
        ]);
        if (!empty($order_return_rows)) {
            $return_ids = array_column($order_return_rows, 'return_id');
            $order_return_items = $this->orderReturnItemRepository->find([
                'order_item_id' => $order_item_id,
                ['return_id', 'IN', $return_ids]
            ]);
            if (!empty($order_return_items)) {
                throw new ErrorException(__('此订单商品正在退款审核！'));
            }
        }

        //订单商品判断
        $order_item_row = $this->orderItemRepository->getOne($order_item_id);
        if (empty($order_item_row)) {
            throw new ErrorException(__('订单商品不存在！'));
        }

        //用户订单权限判断
        $order_info = $this->orderInfoRepository->getOne($order_id);
        if ($order_info['user_id'] != $user_id) {
            throw new ErrorException(__('无操作权限！'));
        }

        $return_row = [
            'order_id' => $order_id,
            'return_refund_amount' => 0,
            'return_refund_point' => 0,
            'store_id' => $order_info['store_id'],
            'buyer_user_id' => $user_id,
            'buyer_store_id' => 0,
            'return_add_time' => getTime(),
            'return_reason_id' => $return_reason_id,
            'return_buyer_message' => $return_buyer_message,
            'return_tel' => $return_tel,
            'return_state_id' => $return_state_id,
            'return_is_paid' => 0,
            'return_is_shipping_fee' => 0,
            'return_shipping_fee' => 0,
            'return_flag' => $return_flag,
            'return_type' => $refund_type,
            'return_commision_fee' => 0
        ];

        $return_item_row = [
            'order_item_id' => $order_item_id,
            'order_id' => $order_id,
            'return_item_num' => $return_item_num,
            'return_item_subtotal' => $return_refund_amount,
            'return_reason_id' => $return_reason_id,
            'return_item_note' => $return_buyer_message,
            'return_item_image' => $return_item_image,
            'return_state_id' => $return_state_id
        ];
        $return_item_row = array_merge($return_item_row, $order_item_row);
        $return_item_rows[] = $return_item_row;

        return $this->addReturnByItem($return_row, $return_item_rows);
    }


    /**
     * 后台代客建退单
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderReturn/add。
     * 前端 api/trade/orderReturn.ts 的 doAdd 一直指向它（OrderReturnEdit 弹窗非编辑态会调用）。
     *
     * 设计：**不重写一套建单逻辑**，而是复用前台同一条链路 addReturn()。
     *   addReturn() 里已经包含了「同一订单商品是否已有审核中的退款单」判断、
     *   订单商品存在性判断、单号生成、明细行组装等全部逻辑；
     *   它唯一的"前台特征"是要求 $user_id 与订单归属一致 ——
     *   后台代客下单时，把订单归属会员的 user_id 传进去即可穿过这道校验，
     *   既复用了全部业务规则，又不会绕过任何一条。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addReturnByAdmin($request)
    {
        $order_id = trim((string)$request->input('order_id', ''));
        if ($order_id === '') {
            throw new ErrorException(__('请输入订单编号'));
        }

        $order_info = $this->orderInfoRepository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__('订单不存在'));
        }

        return $this->addReturn((int)$order_info['user_id'], $request->all());
    }


    /**
     * 修改退单（后台）
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderReturn/edit。
     * 这是**正在被使用**的接口：售后单详情抽屉里的「修改退款金额」
     * （views/trade/orderReturn/components/RefundAmountEdit.vue，经 OrderReturnItem.vue 引入）
     * 就是靠它落库的，目前点一次 404 一次。
     *
     * ⚠️ 两条硬护栏（不是可选项）：
     *   1. 已确认付款（return_is_paid = 1）的退单，不允许再改退款金额与三个确认标记 ——
     *      钱已经出去了，再改数字等于篡改已发生的资金流水；
     *   2. 退款金额不得超过该订单实付金额，且不能为负。
     *
     * ⚠️ 刻意不开放 return_state_id / plantform_return_state_id：
     *    状态流转必须走 review / refused / receive / refund 那四条接口，
     *    那里面还有状态机与会话记录（editNextReturnState）。从编辑口子改状态
     *    会把状态历史改得对不上，事后无法复盘。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function editReturn($request)
    {
        $return_id = (string)$request->input('return_id', '');
        if ($return_id === '') {
            throw new ErrorException(__('数据有误'));
        }

        $return = OrderReturn::find($return_id);
        if (empty($return)) {
            throw new ErrorException(__('退单不存在'));
        }

        $update = [];
        $touch_money = false;

        if ($request->has('return_refund_amount')) {
            $amount = (float)$request->input('return_refund_amount', 0);
            if ($amount < 0) {
                throw new ErrorException(__('退款金额不能为负数'));
            }

            // 不得超过订单实付金额
            $order_base = $this->orderBaseRepository->getOne($return->order_id);
            $paid_amount = $order_base ? (float)$order_base['order_payment_amount'] : 0;
            if ($paid_amount > 0 && $amount > $paid_amount) {
                throw new ErrorException(sprintf(__('退款金额不能超过订单实付金额 %s'), $paid_amount));
            }

            $update['return_refund_amount'] = $amount;
            $touch_money = true;
        }

        // 三个确认标记：确认退款 / 确认结算 / 打款确认 / 财务确认
        foreach (['return_is_paid', 'return_is_settlemented', 'return_withdraw_confirm', 'return_financial_confirm'] as $flag) {
            if ($request->has($flag)) {
                $update[$flag] = $request->boolean($flag) ? 1 : 0;
                $touch_money = true;
            }
        }

        if ($request->has('return_store_message')) {
            $update['return_store_message'] = (string)$request->input('return_store_message', '');
        }

        if (empty($update)) {
            throw new ErrorException(__('没有需要更新的内容'));
        }

        // 护栏 1：钱已付出的退单不能再动金额/确认标记
        if ($touch_money && $return->return_is_paid) {
            throw new ErrorException(__('该退单已确认付款，退款金额与确认标记不允许再修改'));
        }

        $return->fill($update)->save();

        return $return->toArray();
    }


    /**
     * 添加退款单
     * @param $return_row
     * @param $return_item_rows
     * @return array
     * @throws ErrorException
     */
    public function addReturnByItem($return_row, $return_item_rows)
    {
        DB::beginTransaction();

        $order_id = $return_row['order_id'];
        $return_id = $this->numberSeqService->createNextSeq('RT');
        $order_return_num = 0;
        $add_return_items = [];
        foreach ($return_item_rows as $tk => $return_item_row) {
            $item_remain_amount = round(($return_item_row['order_item_payment_amount'] - $return_item_row['order_item_return_agree_amount']), 6);
            if (round($return_item_row['return_item_subtotal'], 6) > $item_remain_amount || $return_item_row['return_item_subtotal'] < 0) {
                throw new ErrorException(__('退单金额错误!'));
            }

            $item_remain_num = $return_item_row['order_item_quantity'] - $return_item_row['order_item_return_agree_num'];
            if ($item_remain_num < $return_item_row['return_item_num']) {
                throw new ErrorException(__('退单商品数量错误!'));
            }

            $order_return_num += $return_item_row['return_item_num'];
            $return_row['return_refund_amount'] = @bcadd($return_row['return_refund_amount'], $return_item_row['return_item_subtotal'], 2);

            //退还佣金计算
            $return_item_rows[$tk]['return_item_commision_fee'] = $return_item_commision_fee = $return_item_row['return_item_subtotal'] * $return_item_row['order_item_commission_rate'] / 100;

            $return_row['return_commision_fee'] = @$return_row['return_commision_fee'] + $return_item_rows[$tk]['return_item_commision_fee'];

            $add_return_items[] = [
                'order_id' => $order_id,
                'return_id' => $return_id,
                'order_item_id' => $return_item_row['order_item_id'],
                'return_item_num' => $return_item_row['return_item_num'],
                'return_reason_id' => $return_item_row['return_reason_id'],
                'return_item_note' => $return_item_row['return_item_note'],
                'return_item_subtotal' => $return_item_row['return_item_subtotal'],
                'return_item_commision_fee' => $return_item_commision_fee,
                'return_item_image' => $return_item_row['return_item_image'],
                'return_state_id' => $return_item_row['return_state_id']
            ];
        }

        //todo 1、保存退款单
        $return_row['return_id'] = $return_id;
        $return_refund_amount = $return_row['return_refund_amount'];
        if (isset($return_row['return_shipping_fee']) && $return_row['return_shipping_fee'] > 0) {
            $return_row['return_refund_amount'] = bcadd($return_row['return_refund_amount'], $return_row['return_shipping_fee'], 2);
        }
        $return_res = $this->repository->add($return_row);
        if (!$return_res) {
            DB::rollBack();
            throw new ErrorException(__('保存退货基础单失败!'));
        }


        //todo 2、保存退款商品
        $return_item_res = $this->orderReturnItemRepository->addBatch($add_return_items);
        if (!$return_item_res) {
            DB::rollBack();
            throw new ErrorException(__('保存退货商品失败!'));
        }

        //todo 3、修改订单信息
        foreach ($return_item_rows as $return_item_row) {
            $order_item_return_num = $return_item_row['return_item_num'] + $return_item_row['order_item_return_num'];
            $order_item_return_subtotal = $return_item_row['order_item_return_subtotal'] + $return_item_row['return_item_subtotal'];
            $order_item_res = $this->orderItemRepository->edit($return_item_row['order_item_id'], array(
                'order_item_return_num' => $order_item_return_num,
                'order_item_return_subtotal' => $order_item_return_subtotal
            ));
            if (!$order_item_res) {
                throw new ErrorException(__('修改订单商品信息失败!'));
            }
        }

        //todo 修改订单数据
        $order_data_row = $this->orderDataRepository->getOne($order_id);
        if (empty($order_data_row)) {
            throw new ErrorException(__('订单数据信息不存在'));
        }
        $order_return_num = $order_data_row['order_return_num'] + $order_return_num;
        $order_data_res = $this->orderDataRepository->edit($order_id, array(
            'order_refund_amount' => ($order_data_row['order_refund_amount'] + $return_refund_amount),
            'order_return_num' => $order_return_num
        ));
        if (!$order_data_res) {
            throw new ErrorException(__('订单数据修改失败!'));
        }

        DB::commit();

        return ['return_id' => $return_id];
    }


    /**
     * 获取退单详情
     * @param $return_id
     * @return false|mixed
     */
    public function getReturnDetail($return_id)
    {
        $return_row = $this->repository->getOne($return_id);
        //收货地址
        if (!empty($return_row)) {
            $order_delivery_address = $this->orderDeliveryAddressRepository->getOne($return_row['order_id']);
            $return_row['da_address'] = $order_delivery_address['da_address'];
            $return_row['da_province'] = $order_delivery_address['da_province'];
            $return_row['da_city'] = $order_delivery_address['da_city'];
            $return_row['da_county'] = $order_delivery_address['da_county'];
            $return_row['da_mobile'] = $order_delivery_address['da_mobile'];
            $return_row['da_name'] = $order_delivery_address['da_name'];
        }

        $items = $this->getReturnItems([$return_row]);

        return current($items);
    }


    /**
     * 取消退单
     * @param $return_id
     * @param $return_row
     * @return true
     * @throws ErrorException
     */
    public function cancel($return_id, $return_row = [])
    {

        DB::beginTransaction();

        if (empty($return_row)) {
            $return_row = $this->repository->getOne($return_id);
        }

        try {
            // 取消退货单
            $this->repository->edit($return_id, ['return_state_id' => StateCode::RETURN_PROCESS_CANCEL]);

            // 取消退货单商品
            $this->orderReturnItemRepository->editWhere(['return_id' => $return_id], ['return_state_id' => StateCode::RETURN_PROCESS_CANCEL]);

            // 修改订单商品数据和申请退款总额
            $return_items = $this->orderReturnItemRepository->find(['return_id' => $return_id]);
            if (!empty($return_items)) {
                $order_item_ids = array_column_unique($return_items, 'order_item_id');
                $order_items = $this->orderItemRepository->gets($order_item_ids);

                foreach ($return_items as $return_item) {
                    // 退货单商品信息
                    $order_item_id = $return_item['order_item_id'];
                    $order_item_row = $order_items[$order_item_id];

                    $return_item_num = $order_item_row['order_item_return_num'] - $return_item['return_item_num'];
                    $return_item_subtotal = $order_item_row['order_item_return_subtotal'] - $return_item['return_item_subtotal'];

                    // 更新订单商品数据
                    $this->orderItemRepository->edit($order_item_id, [
                        'order_item_return_num' => $return_item_num,
                        'order_item_return_subtotal' => $return_item_subtotal
                    ]);
                }
            }

            // 更新订单申请退款总额
            $order_id = $return_row['order_id'];
            $order_data = $this->orderDataRepository->getOne($order_id);
            $order_refund_amount = $order_data['order_refund_amount'] - $return_row['return_refund_amount'];
            $this->orderDataRepository->edit($order_id, ['order_refund_amount' => $order_refund_amount]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('退单操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 更新退单到下一个状态
     * @param $return_id
     * @param $return_state_id
     * @param $next_return_state_id
     * @param $return_state_note
     * @return array|bool
     * @throws ErrorException
     */
    public function editNextReturnState($return_id, $return_state_id, $next_return_state_id, $return_state_note = '')
    {
        //todo 更新退单状态
        $return_data = ['return_state_id' => $next_return_state_id];
        if ($next_return_state_id == StateCode::RETURN_PROCESS_FINISH) {
            $return_data['return_finish_time'] = getDateTime();
            $return_data['return_is_paid'] = 1;
        }
        $flag = $this->repository->edit($return_id, $return_data);
        if (!$flag) {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        //todo 更新退单商品状态
        $flag = $this->orderReturnItemRepository->editWhere(['return_id' => $return_id, 'return_state_id' => $return_state_id],
            ['return_state_id' => $next_return_state_id]);
        if (!$flag) {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return $flag;
    }


    /**
     * 检测是否执行退款操作
     * @param $return_state_id
     * @param $return_next_state_id
     * @return bool
     */
    public static function checkNeedRefund($return_state_id, $return_next_state_id)
    {
        $ids = array(
            StateCode::RETURN_PROCESS_SUBMIT => 1,
            StateCode::RETURN_PROCESS_CHECK => 2,
            StateCode::RETURN_PROCESS_RECEIVED => 3,
            StateCode::RETURN_PROCESS_REFUND => 4,
            StateCode::RETURN_PROCESS_RECEIPT_CONFIRMATION => 5,
            StateCode::RETURN_PROCESS_FINISH => 6,
        );

        return $return_state_id != $return_next_state_id &&
            $ids[$return_state_id] <= $ids[StateCode::RETURN_PROCESS_REFUND] &&
            $ids[$return_next_state_id] >= $ids[StateCode::RETURN_PROCESS_RECEIPT_CONFIRMATION];
    }


    /**
     * 退单审核
     * @param $return_id
     * @param $return_flag
     * @param $return_store_message
     * @param $receiving_address
     * @return true
     * @throws ErrorException
     */
    public function review($return_id, $return_flag = 0, $return_store_message = '', $receiving_address = 0)
    {

        DB::beginTransaction();

        try {

            $return_row = $this->repository->getOne($return_id);
            $return_state_id = $return_row['return_state_id'];
            //todo 获取下个退单状态
            $next_return_state_id = $this->configBaseRepository->getNextReturnStateId($return_state_id);
            if ($return_flag == 0 && $return_state_id == StateCode::RETURN_PROCESS_CHECK) {
                $next_return_state_id = StateCode::RETURN_PROCESS_FINISH;
            }

            $return_update = [];
            //todo 获取店铺的退货地址
            if ($return_flag == 1 && $return_state_id == StateCode::RETURN_PROCESS_CHECK) {
                $return_update = $this->getReceivingAddress($receiving_address, $return_update);
            }

            switch ($return_state_id) {
                case StateCode::RETURN_PROCESS_CHECK:
                    $return_update['return_store_time'] = getDateTime();
                    $return_update['return_store_message'] = $return_store_message;
                    $return_update['return_flag'] = $return_flag;

                    //todo 修改退单数据
                    $this->repository->edit($return_id, $return_update);
                    break;
                default:
                    break;
            }


            //todo 执行退款
            $refund_flag = $this->checkNeedRefund($return_state_id, $next_return_state_id);
            if ($refund_flag) {
                $this->consumeReturnService->doRefund([$return_row]);
            }

            //todo 修改退单状态
            $this->editNextReturnState($return_id, $return_state_id, $next_return_state_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('退单审核操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 退货地址
     * @param $ss_id
     * @param $return_update
     * @return array|mixed
     * @throws ErrorException
     */
    public function getReceivingAddress($ss_id = 0, $return_update = [])
    {
        $row = $this->storeShippingAddressRepository->getOne($ss_id);
        if (empty($row)) {
            throw new ErrorException(__('请选择有效的收货地址 '));
        }

        $return_addr = $row['ss_province'] . $row['ss_city'] . $row['ss_county'] . $row['ss_address'];
        $return_update['return_tel'] = $row['ss_mobile'];
        $return_update['return_addr'] = $return_addr;
        $return_update['return_contact_name'] = $row['ss_name'];

        return $return_update;
    }


    /**
     * 更新退单物流单号
     * @param $request
     * @param $return_id
     * @return mixed
     */
    public function editReturnExpress($request, $return_id)
    {
        $return_update = [
            'express_id' => $request->input('express_id', 0),
            'return_tracking_number' => $request->input('return_tracking_number', ''),
            'return_item_state_id' => $request->input('return_item_state_id', 2040)
        ];
        $flag = $this->repository->edit($return_id, $return_update);

        return $flag;
    }


    /**
     * 拒绝退款
     * @param $return_id
     * @param $return_store_message
     * @return true
     * @throws ErrorException
     */
    public function refused($return_id, $return_store_message = '')
    {

        DB::beginTransaction();

        try {

            $return_row = $this->repository->getOne($return_id);
            $return_state_id = $return_row['return_state_id'];

            //todo 获取下个退单状态
            $next_return_state_id = StateCode::RETURN_PROCESS_REFUSED;

            $return_update = [];
            $return_update['return_store_time'] = getDateTime();
            $return_update['return_store_message'] = $return_store_message;

            //todo 修改退单数据
            $this->repository->edit($return_id, $return_update);

            //todo 修改退单状态
            $this->editNextReturnState($return_id, $return_state_id, $next_return_state_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('退单操作失败: ') . $e->getMessage());
        }

        return true;
    }


}
