<?php

namespace Modules\Pay\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Criteria\UserPointsHistoryCriteria;
use Modules\Pay\Services\UserPointsHistoryService;

class PointsController extends BaseController
{
    private $userPointsHistoryService;
    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserPointsHistoryService $userPointsHistoryService)
    {
        $this->userPointsHistoryService = $userPointsHistoryService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request->merge(['user_id' => $this->userId]);
        $data = $this->userPointsHistoryService->list($request, new UserPointsHistoryCriteria($request));

        return Respond::success($data);
    }

}
