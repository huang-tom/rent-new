<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\UserDistributionService;

class UserDistributionController extends BaseController
{

    private $userDistributionService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(userDistributionService $userDistributionService)
    {
        $this->userDistributionService = $userDistributionService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userDistributionService->getLists($request);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $data = $this->userDistributionService->addDistribution($request);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $user_id = $request->get('user_id', -1);
        $data = $this->userDistributionService->edit($user_id, [
            'user_active' => $request->boolean('user_active'),
        ]);

        return Respond::success($data);
    }


    /**
     * 修改推广员
     *
     * [新增 2026-09-23] Admin 路由表里 `/manage/account/userDistribution/edit` 早已声明，
     * 但控制器里从来没有这个方法 —— Lumen 对"方法不存在"的处理是抛 NotFoundHttpException，
     * 运行时表现为 **404**（不是 500）。这类"路由在、方法没了"的静默 404 契约校验看不出来，
     * 是 route_method_audit.py 专门扫出来的。
     *
     * 说明：编辑只放行推广员自身的属性字段（父级/代理关系/生效状态），
     * **不放行 user_fans_num / user_team_count 这类冗余统计字段** ——
     * 它们由增删粉丝、成团等业务流程维护，允许手工改会让统计和明细对不上。
     */
    public function edit(Request $request)
    {
        $user_id = (int)$request->get('user_id', 0);
        if (!$user_id) {
            return Respond::error(__('推广员编号不能为空'));
        }

        $row = $this->userDistributionService->get($user_id);
        if (empty($row)) {
            return Respond::error(__('推广员不存在'));
        }

        $edit_row = [];
        foreach (['user_parent_id', 'user_partner_id', 'role_level_id', 'ucc_id',
                     'activity_id', 'user_is_da', 'user_is_ca', 'user_is_pa'] as $field) {
            if ($request->has($field)) {
                $edit_row[$field] = $request->input($field);
            }
        }
        foreach (['user_is_sp', 'user_is_pt'] as $field) {
            if ($request->has($field)) {
                $edit_row[$field] = $request->boolean($field);
            }
        }
        if ($request->has('user_active')) {
            $edit_row['user_active'] = $request->boolean('user_active');
        }

        if (empty($edit_row)) {
            return Respond::error(__('没有需要修改的内容'));
        }

        $data = $this->userDistributionService->edit($user_id, $edit_row);

        return Respond::success($data);
    }


}
