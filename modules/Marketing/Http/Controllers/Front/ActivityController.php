<?php

namespace Modules\Marketing\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Marketing\Repositories\Validators\ActivityBaseValidator;
use Modules\Marketing\Services\ActivityBaseService;

class ActivityController extends BaseController
{
    private $activityBaseService;
    private $activityBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ActivityBaseService $activityBaseService, ActivityBaseValidator $activityBaseValidator)
    {
        $this->activityBaseService = $activityBaseService;
        $this->activityBaseValidator = $activityBaseValidator;
    }


    /**
     * 列表
     */
    public function listVoucher(Request $request)
    {
        $user_id = User::getUserId();
        $request['activity_type'] = [1, 2]; //参与类型
        $request['activity_state'] = 1; //活动状态
        $data = $this->activityBaseService->listVoucher($request, $user_id);

        return Respond::success($data);
    }


}

