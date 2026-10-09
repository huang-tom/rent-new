<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserBindConnectCriteria;
use Modules\Account\Services\UserBindConnectService;

class UserBindConnectController extends BaseController
{

    private $userBindConnectService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserBindConnectService $userBindConnectService)
    {
        $this->userBindConnectService = $userBindConnectService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userBindConnectService->list($request, new UserBindConnectCriteria($request));

        return Respond::success($data);
    }

}
