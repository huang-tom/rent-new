<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Repositories\Criteria\OrderInfoCriteria;
use Modules\Trade\Repositories\Criteria\OrderInvoiceCriteria;
use Modules\Trade\Services\OrderCommentService;
use Modules\Trade\Services\OrderInvoiceService;
use Modules\Trade\Services\OrderService;
use Modules\Trade\Services\UserCartService;

class OrderController extends BaseController
{
    private $orderService;
    private $userCartService;
    private $orderCommentService;
    private $orderInvoiceService;

    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderService        $orderService,
        UserCartService     $userCartService,
        OrderCommentService $orderCommentService,
        OrderInvoiceService $orderInvoiceService
    )
    {
        $this->orderService = $orderService;
        $this->userCartService = $userCartService;
        $this->orderCommentService = $orderCommentService;
        $this->orderInvoiceService = $orderInvoiceService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderService->list($request, new OrderInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 详情
     */
    public function detail(Request $request)
    {
        $request['user_id'] = $this->userId;
        $order_id = $request->input('order_id');
        $data = $this->orderService->detail($order_id);

        return Respond::success($data);
    }


    /**
     * 获取用户中心订单数量
     */
    public function getOrderNum(Request $request)
    {
        $data = $this->orderService->getOrderStatisticsInfo($this->userId);

        return Respond::success($data);
    }


    /**
     * 添加订单
     */
    public function add(Request $request)
    {
        $cart_data = $request->all();
        $cart_data['user_id'] = $this->userId;
        $cart_data['cart_select'] = 1;
        $cart_data['site_id'] = $request->input('site_id', 0);
        $cart_data['delivery_time_id'] = $request->input('delivery_time_id', 0);
        $cart_data['invoice_type_id'] = $request->input('invoice_type_id', 0);
        $cart_data['order_invoice_title'] = $request->input('order_invoice_title', '');
        $cart_data['user_invoice_id'] = $request->input('user_invoice_id', 0);
        $cart_data['salesperson_id'] = $request->input('salesperson_id', 0);
        $cart_data['virtual_service_date'] = $request->input('virtual_service_date', '');
        $cart_data['virtual_service_time'] = $request->input('virtual_service_time', '');

        $store_rows = $this->userCartService->checkout($request, $this->userId);
        $cart_data = array_merge($cart_data, $store_rows);

        $cart_data = $this->orderService->addOrder($cart_data, $this->userId);

        return Respond::success($cart_data);
    }


    /**
     * 取消订单
     */
    public function cancel(Request $request)
    {
        $order_id = $request->input('order_id', '');
        $this->orderService->checkUserOrder($this->userId, $order_id);

        $flag = $this->orderService->cancel($order_id);
        if ($flag) {
            return Respond::success([]);
        } else {
            return Respond::error(__('取消订单失败'));
        }
    }


    /**
     * 确认收货
     */
    public function receive(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderService->receive($order_id);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

    }


    /**
     * 订单商品评价
     */
    public function storeEvaluationWithContent(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderCommentService->storeEvaluationWithContent($order_id, $this->userId);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }
    }


    /**
     * 添加订单评论
     */
    public function addOrderComment(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderCommentService->addOrderComment($this->userId, $request);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }
    }


    /**
     * 订单发票列表
     */
    public function listInvoice(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderInvoiceService->list($request, new OrderInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 添加订单发票
     */
    public function addOrderInvoice(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderService->addOrderInvoice($request);

        return Respond::success($data);
    }


}
