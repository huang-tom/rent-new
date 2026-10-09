<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Services\OrderLogisticsService;

class LogisticsController extends BaseController
{
    private $orderLogisticsService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderLogisticsService $orderLogisticsService)
    {
        $this->orderLogisticsService = $orderLogisticsService;

        $this->userId = User::getUserId();
    }


    /**
     * 订单物流
     */
    public function trace(Request $request)
    {
        $data = $this->orderLogisticsService->trace($request);

        return Respond::success($data);
    }


}
