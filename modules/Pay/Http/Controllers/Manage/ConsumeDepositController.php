<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Repositories\Criteria\ConsumeDepositCriteria;
use Modules\Pay\Services\ConsumeDepositService;

class ConsumeDepositController extends BaseController
{
    private $consumeDepositService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsumeDepositService $consumeDepositService)
    {
        $this->consumeDepositService = $consumeDepositService;
    }


    //列表
    public function list(Request $request)
    {
        $data = $this->consumeDepositService->list($request, new ConsumeDepositCriteria($request));

        return Respond::success($data);
    }


    //线下支付
    public function offlinePay(Request $request)
    {
        $data = $this->consumeDepositService->offlinePay($request);

        return Respond::success($data);
    }


    /**
     * 收款确认（列表「收款确认」开关）
     *
     * [新增 2026-09-23] 补 POST /manage/pay/consumeDeposit/editReview（原为 404）。
     */
    public function editReview(Request $request)
    {
        $data = $this->consumeDepositService->editReview($request);

        return Respond::success($data);
    }


}
