<?php

namespace Modules\Marketing\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Marketing\Repositories\Criteria\ActivityItemCriteria;
use Modules\Marketing\Repositories\Validators\ActivityItemValidator;
use Modules\Marketing\Services\ActivityItemService;

class ActivityItemController extends BaseController
{
    private $activityItemService;
    private $activityItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ActivityItemService   $activityItemService,
        ActivityItemValidator $activityItemValidator
    )
    {
        $this->activityItemService = $activityItemService;
        $this->activityItemValidator = $activityItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->activityItemService->list($request, new ActivityItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取活动商品
     */
    public function getActivityBuyItems(Request $request)
    {
        $activity_id = $request->input('activity_id');
        $data = $this->activityItemService->getActivityBuyItems($activity_id);

        return Respond::success($data);
    }


    /**
     * 新增活动商品
     */
    public function addActivityBuyItems(Request $request)
    {
        $msg = '';
        $this->activityItemValidator->with($request->all())->passesOrFail('create');
        $count = $this->activityItemService->addActivityBuyItems($request, $msg);

        return Respond::success([$count], __("成功添加") . $count . __("个商品") . "\n" . $msg);
    }


    /**
     * 修改活动商品价格
     */
    public function editActivityItem(Request $request)
    {

        $this->activityItemValidator->with($request->all())->passesOrFail('update');
        $data = $this->activityItemService->editActivityItem($request);

        return Respond::success($data);
    }


    /**
     * 删除活动商品
     */
    public function removeActivityBuyItems(Request $request)
    {
        $data = $this->activityItemService->removeItem($request);

        return Respond::success($data);
    }


    /**
     * 统一设置活动商品折扣
     */
    public function editBatchPrice(Request $request)
    {
        $activity_id = $request->input('activity_id', 0);
        $discount = $request->input('discount', 0);

        $data = $this->activityItemService->editBatchPrice($activity_id, $discount);
        return Respond::success($data);
    }


}
