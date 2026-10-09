<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Repositories\Criteria\ConsumeWithdrawCriteria;
use Modules\Pay\Services\ConsumeWithdrawService;

class ConsumeWithdrawController extends BaseController
{
    private $consumeWithdrawService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsumeWithdrawService $consumeWithdrawService)
    {
        $this->consumeWithdrawService = $consumeWithdrawService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->consumeWithdrawService->list($request, new ConsumeWithdrawCriteria($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $withdraw_id = $request->input('withdraw_id', -1);
        $data = $this->consumeWithdrawService->edit($withdraw_id, [
            'withdraw_state' => $request->input('withdraw_state', 0),   //审核状态
            'withdraw_bankflow' => $request->input('withdraw_bankflow', ''),   //银行流水号
            'withdraw_time' => $request->input('withdraw_time', ''), //审核时间
            'withdraw_desc' => $request->input('withdraw_desc', '') //描述
        ]);

        return Respond::success($data);
    }

}
