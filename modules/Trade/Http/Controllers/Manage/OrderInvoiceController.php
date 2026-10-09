<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Repositories\Criteria\OrderInvoiceCriteria;
use Modules\Trade\Repositories\Validators\OrderInvoiceValidator;
use Modules\Trade\Services\OrderInvoiceService;

class OrderInvoiceController extends BaseController
{
    private $orderInvoiceService;
    private $orderInvoiceValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderInvoiceService $orderInvoiceService, OrderInvoiceValidator $orderInvoiceValidator)
    {
        $this->orderInvoiceService = $orderInvoiceService;
        $this->orderInvoiceValidator = $orderInvoiceValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->orderInvoiceService->list($request, new OrderInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     * [新增 2026-09-22] 前端 api/trade/orderInvoice.ts 的 doAdd() 一直指向，原先未注册
     */
    public function add(Request $request)
    {
        $data = $this->orderInvoiceService->addInvoice($request);

        return Respond::success($data);
    }


    /**
     * 修改
     * [新增 2026-09-22] 前端 doEdit() 一直指向，原先未注册
     */
    public function edit(Request $request)
    {
        $data = $this->orderInvoiceService->editInvoice($request);

        return Respond::success($data);
    }


    /**
     * 删除（支持单个 / 批量）
     * [新增 2026-09-22] 前端 doRemove()/doRemoveBatch() 都指向这条路由，原先未注册
     */
    public function remove(Request $request)
    {
        $data = $this->orderInvoiceService->removeInvoice($request);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editStatus(Request $request)
    {
        $data = $this->orderInvoiceService->edit($request['order_invoice_id'], [
            'invoice_img' => $request->input('invoice_img', ''),
            'invoice_status' => true
        ]);

        return Respond::success($data);
    }

}
