<?php

namespace Modules\Analytics\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Analytics\Services\AnalyticsReturnService;

class AnalyticsReturnController extends BaseController
{
    private $analyticsReturnService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        AnalyticsReturnService $analyticsReturnService
    )
    {
        $this->analyticsReturnService = $analyticsReturnService;
    }


    /**
     * 统计退款数量
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnNum(Request $request)
    {
        $data = $this->analyticsReturnService->getReturnNum($request);

        return Respond::success($data);
    }


    /**
     * 统计退款金额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnAmount(Request $request)
    {
        $data = $this->analyticsReturnService->getReturnAmount($request);

        return Respond::success($data);
    }


    /**
     * 根据时间统计退款金额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnAmountTimeline(Request $request)
    {
        $data = $this->analyticsReturnService->getReturnAmountTimeline($request);

        return Respond::success($data);
    }


    public function getReturnNumTimeline(Request $request)
    {
        $data = $this->analyticsReturnService->getReturnNumTimeline($request);

        return Respond::success($data);
    }


}
