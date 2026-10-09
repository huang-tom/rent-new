<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Services\UserInfoService;
use Modules\Pay\Repositories\Criteria\UserPointsHistoryCriteria;
use Modules\Pay\Services\UserPointsHistoryService;

class UserPointsHistoryController extends BaseController
{
    private $userPointsHistoryService;
    private $userInfoService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserPointsHistoryService $userPointsHistoryService, UserInfoService $userInfoService)
    {
        $this->userPointsHistoryService = $userPointsHistoryService;
        $this->userInfoService = $userInfoService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userPointsHistoryService->list($request, new UserPointsHistoryCriteria($request));
        if (!empty($data['data'])) {
            $data['data'] = $this->userInfoService->fixUserInfo($data['data']);
        }

        return Respond::success($data);
    }


}
