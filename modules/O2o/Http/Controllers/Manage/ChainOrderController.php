<?php

namespace Modules\O2o\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Repositories\Criteria\OrderInfoCriteria;
use Modules\Trade\Services\OrderService;

class ChainOrderController extends BaseController
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }


    /**
     * 门店订单列表
     * 入参：chain_id（门店，可选=全部）、order_state_id、order_id、order_title、
     *       user_id、order_stime、order_etime、page、size
     */
    public function list(Request $request)
    {
        $data = $this->orderService->list($request, new OrderInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 门店订单详情
     */
    public function detail(Request $request)
    {
        $order_id = $request->input('order_id', '');
        if (empty($order_id)) {
            throw new ErrorException(__('订单号不能为空'));
        }

        $data = $this->orderService->detail($order_id);

        return Respond::success($data);
    }


    /**
     * 门店订单取消
     */
    public function cancel(Request $request)
    {
        $order_id = $request->input('order_id', '');
        if (empty($order_id)) {
            throw new ErrorException(__('订单号不能为空'));
        }

        $note = $request->input('order_cancel_reason', __('门店后台取消'));
        $data = $this->orderService->cancel($order_id, $note);

        return Respond::success($data);
    }
}
