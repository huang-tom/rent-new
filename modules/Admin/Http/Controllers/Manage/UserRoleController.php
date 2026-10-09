<?php

namespace Modules\Admin\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Admin\Repositories\Criteria\UserRoleCriteria;
use Modules\Admin\Repositories\Validators\UserRoleValidator;
use Modules\Admin\Services\UserRoleService;

class UserRoleController extends BaseController
{
    private $userRoleService;
    private $userRoleValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRoleService $userRoleService, UserRoleValidator $userRoleValidator)
    {
        $this->userRoleService = $userRoleService;
        $this->userRoleValidator = $userRoleValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userRoleService->list($request, new UserRoleCriteria($request));

        return Respond::success($data);
    }


    public function formatRequest($request)
    {
        return [
            'user_role_name' => $request->input('user_role_name'),
            'user_role_code' => $request->input('user_role_code'),
            'menu_ids' => $request->input('menu_ids'),
            'user_role_buildin' => $request->boolean('user_role_buildin', false)
        ];
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userRoleValidator->with($request->all())->passesOrFail('create');
        $data = $this->userRoleService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_role_id = $request['user_role_id'];
        $this->userRoleValidator->setId($user_role_id);
        $this->userRoleValidator->with($request->all())->passesOrFail('update');
        $data = $this->userRoleService->edit($user_role_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_role_id = $request->input('user_role_id', -1);
        $data = $this->userRoleService->removeRole($user_role_id);

        return Respond::success($data);
    }

}
