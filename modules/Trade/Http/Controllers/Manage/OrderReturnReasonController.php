<?php

namespace Modules\Trade\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Trade\Repositories\Criteria\OrderReturnReasonCriteria;
use Modules\Trade\Repositories\Validators\OrderReturnReasonValidator;
use Modules\Trade\Services\OrderReturnReasonService;

class OrderReturnReasonController extends BaseController
{
    private $orderReturnReasonService;
    private $orderReturnReasonValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderReturnReasonService $orderReturnReasonService, OrderReturnReasonValidator $orderReturnReasonValidator)
    {
        $this->orderReturnReasonService = $orderReturnReasonService;
        $this->orderReturnReasonValidator = $orderReturnReasonValidator;
    }


    public function list(Request $request)
    {
        $data = $this->orderReturnReasonService->list($request, new OrderReturnReasonCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->orderReturnReasonValidator->with($request->all())->passesOrFail('create');
        $data = $this->orderReturnReasonService->add([
            'return_reason_name' => $request->input('return_reason_name', ''),
            'return_reason_sort' => $request->input('return_reason_sort', 255),
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $return_reason_id = $request['return_reason_id'];

        $this->orderReturnReasonValidator->setId($return_reason_id);
        $this->orderReturnReasonValidator->with($request->all())->passesOrFail('update');
        $data = $this->orderReturnReasonService->edit($return_reason_id, [
            'return_reason_name' => $request->input('return_reason_name', ''),
            'return_reason_sort' => $request->input('return_reason_sort', 255),
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->orderReturnReasonService->remove($request['return_reason_id']);

        return Respond::success($data);
    }

}
