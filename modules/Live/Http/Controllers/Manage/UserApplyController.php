<?php

namespace Modules\Live\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Live\Repositories\Criteria\UserApplyCriteria;
use Modules\Live\Services\UserApplyService;

class UserApplyController extends BaseController
{
    private $userApplyService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserApplyService $userApplyService)
    {
        $this->userApplyService = $userApplyService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userApplyService->list($request, new UserApplyCriteria($request));

        return Respond::success($data);
    }

}
