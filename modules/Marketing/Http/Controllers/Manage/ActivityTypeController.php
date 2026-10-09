<?php

namespace Modules\Marketing\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Marketing\Repositories\Criteria\ActivityTypeCriteria;
use Modules\Marketing\Services\ActivityTypeService;

class ActivityTypeController extends BaseController
{
    private $activityTypeService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ActivityTypeService $activityTypeService)
    {
        $this->activityTypeService = $activityTypeService;
    }


    /**
     * 活动类型字典列表（供营销活动表单下拉使用）
     */
    public function list(Request $request)
    {
        $data = $this->activityTypeService->list($request, new ActivityTypeCriteria($request));

        return Respond::success($data);
    }

}
