<?php

namespace Modules\Trade\Http\Controllers\Manage;

use Modules\Account\Repositories\Models\User;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Repositories\Criteria\NewOrderCriteria;
use Modules\Trade\Services\NewOrderService;

class NewOrderController extends BaseController
{
    private $newOrderService;

    public function __construct(NewOrderService $newOrderService)
    {
        $this->newOrderService = $newOrderService;
    }

    /**
     * 购买订单列表
     * keyword / order_source / order_status / order_time_start / order_time_end
     */
    public function list(Request $request)
    {
        $data = $this->newOrderService->list($request, new NewOrderCriteria($request));

        return Respond::success($data);
    }

    /**
     * 订单详情
     */
    public function get(Request $request)
    {
        $order_id = (int)$request->get('order_id', 0);
        $data = $this->newOrderService->getOrder($order_id);

        return Respond::success($data);
    }

    /**
     * 新增购买订单
     */
    public function add(Request $request)
    {
        $payload = $request->all();
        $payload['user_id'] = User::getUserId();
        $data = $this->newOrderService->addOrder($payload);

        return Respond::success($data);
    }

    /**
     * 分仓：保存仓编码+名称
     */
    public function assignWarehouse(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $warehouse_code = $request->input('warehouse_code', '');
        $warehouse_name = $request->input('warehouse_name', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newOrderService->assignWarehouse($order_id, $warehouse_code, $warehouse_name, $operator_name);

        return Respond::success($data);
    }

    /**
     * 打标（人工输入 tag_name）
     */
    public function addTag(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $tag_name = $request->input('tag_name', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newOrderService->addTag($order_id, $tag_name, $operator_name);

        return Respond::success($data);
    }

    /**
     * 删除标签
     */
    public function removeTag(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $tag_id = (int)$request->input('tag_id', 0);
        $operator_name = $request->input('operator_name', '');
        $data = $this->newOrderService->removeTag($order_id, $tag_id, $operator_name);

        return Respond::success($data);
    }

    /**
     * 客服备注（写入订单主表 service_remark）
     */
    public function addRemark(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $remark_content = $request->input('remark_content', '');
        if ($remark_content === '' || $remark_content === null) {
            $remark_content = $request->input('service_remark', '');
        }
        $operator_name = $request->input('operator_name', '');
        $data = $this->newOrderService->addRemark($order_id, $remark_content, $operator_name);

        return Respond::success($data);
    }

    /**
     * 退款
     */
    public function refund(Request $request)
    {
        $order_id = (int)$request->input('order_id', 0);
        $refund_amount = $request->input('refund_amount', 0);
        $refund_reason = $request->input('refund_reason', '');
        $operator_name = $request->input('operator_name', '');
        $data = $this->newOrderService->refund($order_id, $refund_amount, $refund_reason, $operator_name);

        return Respond::success($data);
    }
}
