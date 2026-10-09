<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Services\DistributionOrderService;

class DistributionController extends BaseController
{
    private $distributionOrderService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DistributionOrderService $distributionOrderService)
    {
        $this->distributionOrderService = $distributionOrderService;

        $this->userId = User::getUserId();
    }


    /**
     * 订单物流
     */
    public function listsOrder(Request $request)
    {
        $request['user_id'] = $this->userId;
        $request['size'] = $request['size'] ?? 10;
        $data = $this->distributionOrderService->listsOrder($request);

        return Respond::success($data);
    }


}
