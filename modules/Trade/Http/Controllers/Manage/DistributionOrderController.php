<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Repositories\Criteria\DistributionOrderCriteria;
use Modules\Trade\Services\DistributionOrderService;

class DistributionOrderController extends BaseController
{
    private $distributionOrderService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DistributionOrderService $distributionOrderService)
    {
        $this->distributionOrderService = $distributionOrderService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->distributionOrderService->list($request, new DistributionOrderCriteria($request));

        return Respond::success($data);
    }

}
