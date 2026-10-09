<?php

namespace Modules\Trade\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Trade\Repositories\Criteria\OrderReturnCriteria;
use Modules\Trade\Services\OrderReturnService;

class OrderReturnController extends BaseController
{
    private $orderReturnService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->orderReturnService->list($request, new OrderReturnCriteria($request));

        return Respond::success($data);
    }


    /**
     * 退单详情
     */
    public function getByReturnId(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $data = $this->orderReturnService->getReturnDetail($return_id);
        $data['return_item_list'] = $data['items'];

        return Respond::success($data);
    }


    /**
     * 新增退单（后台代客建单）
     * [新增 2026-09-22] 前端 doAdd() 一直指向，原先未注册
     */
    public function add(Request $request)
    {
        $data = $this->orderReturnService->addReturnByAdmin($request);

        return Respond::success($data);
    }


    /**
     * 修改退单（修改退款金额 / 确认标记）
     * [新增 2026-09-22] 「修改退款金额」弹窗一直在调，原先未注册 → 点一次 404 一次
     */
    public function edit(Request $request)
    {
        $data = $this->orderReturnService->editReturn($request);

        return Respond::success($data);
    }


    /**
     * 退单审核
     */
    public function review(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_flag = $request->input('return_flag', 0);
        $return_store_message = $request->input('return_store_message', 0);
        $receiving_address = $request->input('receiving_address', 0);

        $data = $this->orderReturnService->review($return_id, $return_flag, $return_store_message, $receiving_address);

        return Respond::success([$data]);
    }


    /**
     * 退单确认收货
     */
    public function receive(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $data = $this->orderReturnService->review($return_id);

        return Respond::success([$data]);
    }


    /**
     * 退单确认付款
     */
    public function refund(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $data = $this->orderReturnService->review($return_id);

        return Respond::success([$data]);
    }


    /**
     * 拒绝退款
     */
    public function refused(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_store_message = $request->input('return_store_message', 0);
        $data = $this->orderReturnService->refused($return_id, $return_store_message);

        return Respond::success([$data]);
    }

}
