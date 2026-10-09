<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Services\UserInfoService;
use Modules\Pay\Repositories\Criteria\UserResourceCriteria;
use Modules\Pay\Services\UserResourceService;

class UserResourceController extends BaseController
{
    private $userResourceService;
    private $userInfoService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserResourceService $userResourceService, UserInfoService $userInfoService)
    {
        $this->userResourceService = $userResourceService;
        $this->userInfoService = $userInfoService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userResourceService->list($request, new UserResourceCriteria($request));
        if (!empty($data['data'])) {
            $data['data'] = $this->userInfoService->fixUserInfo($data['data']);
        }

        return Respond::success($data);
    }


    /**
     * 修改用户资金
     */
    public function updateUserMoney(Request $request)
    {
        $data = $this->userResourceService->updateUserMoney($request);

        return Respond::success($data);
    }


    /**
     * 修改用户积分
     */
    public function updatePoints(Request $request)
    {
        $data = $this->userResourceService->updateUserPoints($request);

        return Respond::success($data);
    }

}
