<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Repositories\Criteria\NewRentOrderCriteria;
use Modules\Trade\Services\NewRentOrderService;

class NewRentOrderController extends BaseController
{
    private $newRentOrderService;

    public function __construct(NewRentOrderService $newRentOrderService)
    {
        $this->newRentOrderService = $newRentOrderService;
    }

    /**
     * 租赁订单列表
     * keyword / order_source / order_status / rent_days / order_time_start / order_time_end
     */
    public function list(Request $request)
    {
        $data = $this->newRentOrderService->list($request, new NewRentOrderCriteria($request));

        return Respond::success($data);
    }

    public function get(Request $request)
    {
        $order_id = (int)$request->get('order_id', 0);
        $data = $this->newRentOrderService->getOrder($order_id);

        return Respond::success($data);
    }

    public function add(Request $request)
    {
        $payload = $request->all();
        $payload['user_id'] = User::getUserId();
        $data = $this->newRentOrderService->addOrder($payload);

        return Respond::success($data);
    }

    public function assignWarehouse(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $warehouse_code = $request->input('warehouse_code', '');
        $warehouse_name = $request->input('warehouse_name', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->assignWarehouse($order_id, $warehouse_code, $warehouse_name, $operator_name);

        return Respond::success($data);
    }

    public function addTag(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $tag_name = $request->input('tag_name', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->addTag($order_id, $tag_name, $operator_name);

        return Respond::success($data);
    }

    public function removeTag(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $tag_id = (int)$request->input('tag_id', 0);
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->removeTag($order_id, $tag_id, $operator_name);

        return Respond::success($data);
    }

    public function addRemark(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $remark_content = $request->input('remark_content', '');
        if ($remark_content === '' || $remark_content === null) {
            $remark_content = $request->input('service_remark', '');
        }
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->addRemark($order_id, $remark_content, $operator_name);

        return Respond::success($data);
    }

    public function refund(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $refund_amount = $request->input('refund_amount', 0);
        $refund_reason = $request->input('refund_reason', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->refund($order_id, $refund_amount, $refund_reason, $operator_name);

        return Respond::success($data);
    }

    /**
     * 退租 / 提前归还
     */
    public function returnRent(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $return_date = $request->input('return_date', null);
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->returnRent($order_id, $return_date, $operator_name);

        return Respond::success($data);
    }

    /**
     * 续租
     */
    public function renew(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $renew_days = (int)$request->input('renew_days', 0);
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->renew($order_id, $renew_days, $operator_name);

        return Respond::success($data);
    }

    /**
     * 买断
     */
    public function buyout(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $buyout_price = $request->input('buyout_price', null);
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->buyout($order_id, $buyout_price, $operator_name);

        return Respond::success($data);
    }

    /**
     * 报修
     */
    public function repair(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $fault_type = $request->input('fault_type', '');
        $fault_desc = $request->input('fault_desc', '');
        $handle_method = $request->input('handle_method', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newRentOrderService->repair($order_id, $fault_type, $fault_desc, $handle_method, $operator_name);

        return Respond::success($data);
    }
}
