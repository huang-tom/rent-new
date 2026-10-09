<?php

namespace Modules\Marketing\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Marketing\Repositories\Criteria\ActivityBaseCriteria;
use Modules\Marketing\Repositories\Validators\ActivityBaseValidator;
use Modules\Marketing\Services\ActivityBaseService;

class ActivityBaseController extends BaseController
{
    private $activityBaseService;
    private $activityBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ActivityBaseService   $activityBaseService,
        ActivityBaseValidator $activityBaseValidator
    )
    {
        $this->activityBaseService = $activityBaseService;
        $this->activityBaseValidator = $activityBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->activityBaseService->list($request, new ActivityBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     *
     * [本地补齐 2026-09-22] 厂商原实现只映射 8 个字段，activity_sort /
     * activity_state / activity_remark / activity_effective_quantity 全部被丢弃
     * —— 前端填的排序、备注、初始状态保存后一律变回默认值（静默丢数据）。
     * 现补齐这 4 个字段；activity_rule 的 json_decode 也加了非法 JSON 保护，
     * 否则前端传一个坏 JSON 会让整条记录写不进去。
     */
    public function formatRequest($request)
    {
        $activity_rule = $request->input('activity_rule', '');
        $rule = [];
        if (is_array($activity_rule)) {
            $rule = $activity_rule;
        } elseif (is_string($activity_rule) && $activity_rule !== '') {
            $decoded = json_decode($activity_rule, true);
            if (is_array($decoded)) {
                $rule = $decoded;
            }
        }

        $data = [
            'activity_name' => $request['activity_title'],  //活动名称
            'activity_title' => $request['activity_title'],  //活动名称
            'activity_type_id' => $request->input('activity_type_id', 0),   //活动类型
            'activity_type' => $request->input('activity_type', 1),      //参与类型(ENUM):1-免费参与;2-积分参与;3-购买参与;4-分享参与
            'activity_starttime' => $request->input('activity_starttime', 0), //活动开始时间(Unix 秒)
            'activity_endtime' => $request->input('activity_endtime', 0),    //活动结束时间(Unix 秒)
            'activity_use_level' => $request->input('activity_use_level', ''), //会员等级
            'activity_rule' => $rule, //活动规则(JSON)
            // ↓ 本地补齐
            'activity_sort' => $request->input('activity_sort', 50),
            'activity_state' => $request->input('activity_state', 1),
            'activity_remark' => $request->input('activity_remark', ''),
            'activity_effective_quantity' => $request->input('activity_effective_quantity', 0),
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->activityBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->activityBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $activity_id = $request['activity_id'];
        $this->activityBaseValidator->setId($activity_id);
        $this->activityBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->activityBaseService->edit($activity_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $activity_id = $request['activity_id'];
        $data = $this->activityBaseService->remove($activity_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $data = $this->activityBaseService->editState($request);

        return Respond::success($data);
    }


}
