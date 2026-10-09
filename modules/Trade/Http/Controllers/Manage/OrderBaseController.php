<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Trade\Repositories\Criteria\OrderInfoCriteria;
use Modules\Trade\Repositories\Criteria\OrderStateLogCriteria;
use Modules\Trade\Services\OrderService;
use Modules\Trade\Services\OrderStateLogService;
use Modules\Trade\Services\UserCartService;

class OrderBaseController extends BaseController
{
    private $orderService;
    private $orderStateLogService;
    private $userCartService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderService          $orderService,
        OrderStateLogService  $orderStateLogService,
        UserCartService       $userCartService
    ) {
        $this->orderService = $orderService;
        $this->orderStateLogService = $orderStateLogService;
        $this->userCartService = $userCartService;
    }


    /**
     * 后台代客下单
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderBase/add。
     * 订单列表左上角的「添加」按钮是**活的**（views/trade/orderBase/index.vue:5，
     * 没有 v-if="false"），点开后 OrderBaseEdit 弹窗提交 {user_id, ud_id, product_items}，
     * 但这个接口从来没实现过 —— 也就是说后台根本建不了订单。
     *
     * 为什么不在服务里另写一套建单逻辑：
     *   OrderService::addOrder() 需要的是一份**已经算好价格**的结算数据
     *   （items 按店铺分组、每项含 money_amount / product_amount / voucher_items …），
     *   那份数据是 UserCartService::checkout() 的产物，里面包含会员等级折扣、
     *   优惠券分摊、运费规则、积分抵扣等全部计价规则。
     *   自己再手写一遍等于把计价引擎重写一次，是这个项目里最不能出错的地方。
     *
     * 复用方式（关键）：checkout() 支持「不从购物车读」——
     *   入参 cart_id = "商品ID|数量|购物车ID"，第三段传 0 时 from_cart=0，
     *   它直接拿这份明细去计价（UserCartService.php:290 `if ($cart_id == 0)`）。
     *   于是后台下单既能走完整的计价+建单链路，又**完全不碰该会员的购物车**。
     */
    public function add(Request $request)
    {
        $user_id = (int)$request->input('user_id', 0);
        if ($user_id <= 0) {
            throw new ErrorException(__('请选择买家'));
        }
        $ud_id = (int)$request->input('ud_id', 0);

        $raw = $request->input('product_items', '[]');
        $product_items = is_array($raw) ? $raw : json_decode((string)$raw, true);
        if (!is_array($product_items) || empty($product_items)) {
            throw new ErrorException(__('请先添加商品'));
        }

        $parts = [];
        foreach ($product_items as $item) {
            $item_id = (int)($item['item_id'] ?? 0);
            $cart_quantity = (int)($item['cart_quantity'] ?? 0);
            if ($item_id <= 0) {
                continue;
            }
            if ($cart_quantity <= 0) {
                throw new ErrorException(__('购买数量最低为 1 哦~'));
            }
            $parts[] = $item_id . '|' . $cart_quantity . '|0';
        }
        if (empty($parts)) {
            throw new ErrorException(__('商品明细无效'));
        }

        $checkout_request = $request->duplicate();
        $checkout_request->merge([
            'cart_id' => implode(',', $parts),
            'ud_id' => $ud_id,
            // 后台选的是会员的收货地址 → 这是一张要发货的订单，
            // 置 true 后 formatCartRows() 才会去解析收货地址并套用运费规则
            'is_delivery' => $ud_id > 0,
        ]);

        $cart_data = $this->userCartService->checkout($checkout_request, $user_id);

        // ⚠️ OrderService::addOrder() 对下面这些键是直接下标读取、没有 ?? 兜底
        //    （OrderService.php:199-216）。缺任何一个都会触发 "Undefined array key" 警告，
        //    而 Lumen 的异常处理器会把警告升级成 ErrorException → 整个请求 500。
        //    前台由 OrderController@add 逐个补齐，后台这里也必须补。
        $cart_data = array_merge([
            'chain_id' => 0,
            'site_id' => 0,
            'user_voucher_ids' => [],
            'redemption_ids' => [],
            'delivery_type_id' => 0,
            'is_delivery' => false,
            'payment_type_id' => 0,
            'delivery_time_id' => 0,
            'invoice_type_id' => 0,
            'order_invoice_title' => '',
            'order_message' => (string)$request->input('order_message', ''),
            'virtual_service_date' => '',
            'virtual_service_time' => '',
            'salesperson_id' => 0,
            'user_invoice_id' => 0,
            'currency_id' => 86,
        ], $cart_data);

        $cart_data['user_id'] = $user_id;
        $cart_data['ud_id'] = $ud_id;

        $data = $this->orderService->addOrder($cart_data, $user_id);

        return Respond::success($data);
    }


    /**
     * 修改订单运费
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderBase/editShoppingFee。
     * 订单详情里「修改邮费」（views/trade/orderBase/components/ShippingFeeEdit.vue，
     * 经 OnlineOrderItem.vue 引入）就是在调它，目前点一次 404 一次。
     */
    public function editShoppingFee(Request $request)
    {
        $order_id = trim((string)$request->input('order_id', ''));
        if ($order_id === '') {
            throw new ErrorException(__('订单号有误！'));
        }
        if (!$request->has('fee_amount')) {
            throw new ErrorException(__('请输入运费金额'));
        }
        if (!is_numeric($request->input('fee_amount'))) {
            throw new ErrorException(__('运费金额必须是数字'));
        }

        $data = $this->orderService->editShoppingFee($order_id, (float)$request->input('fee_amount'));

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->orderService->list($request, new OrderInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 详情
     */
    public function detail(Request $request)
    {
        $order_id = $request['order_id'];
        $data = $this->orderService->detail($order_id);

        return Respond::success($data);
    }


    /**
     * 日志列表
     */
    public function listStateLog(Request $request)
    {
        $data = $this->orderStateLogService->list($request, new OrderStateLogCriteria($request));

        return Respond::success($data);
    }


    /**
     * 订单审核
     */
    public function review(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->review($request->get('order_id'));
        } else {
            throw new ErrorException('订单号有误！');
        }

        return Respond::success($data);
    }


    /**
     * 财务审核
     */
    public function finance(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->finance($request->get('order_id'));
        } else {
            throw new ErrorException('订单号有误！');
        }

        return Respond::success($data);
    }


    /**
     * 出库
     */
    public function picking(Request $request)
    {
        $data = $this->orderService->picking($request);

        return Respond::success($data);
    }


    /**
     * 发货操作
     */
    public function shipping(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->shipping($request);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }


    /**
     * 确认收货
     */
    public function receive(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->receive($request->get('order_id'));
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }


    /**
     * 取消订单
     */
    public function cancel(Request $request)
    {
        if ($request->has('order_id')) {
            $order_state_note = $request->input('order_cancel_reason', '');
            $data = $this->orderService->cancel($request->get('order_id'), $order_state_note);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }

}
