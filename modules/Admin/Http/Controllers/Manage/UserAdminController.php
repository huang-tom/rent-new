<?php

namespace Modules\Admin\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\UserService;
use Modules\Admin\Repositories\Criteria\UserAdminCriteria;
use Modules\Admin\Repositories\Validators\UserAdminValidator;
use Modules\Admin\Services\UserAdminService;

class UserAdminController extends BaseController
{
    private $userService;
    private $userAdminService;
    private $userAdminValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserService        $userService,
        UserAdminService   $userAdminService,
        UserAdminValidator $userAdminValidator
    )
    {
        $this->userService = $userService;
        $this->userAdminService = $userAdminService;
        $this->userAdminValidator = $userAdminValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userAdminService->list($request, new UserAdminCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userAdminValidator->with($request->all())->passesOrFail('create');
        $user_id = $request->input('user_id', -1);
        $user_base = $this->userService->get($user_id);
        if (empty($user_base)) {
            throw new ErrorException(__('当前用户编号不存在'));
        }

        $user_admin = $this->userAdminService->get($user_id);
        if (!empty($user_admin)) {
            throw new ErrorException(__('系统用户已存在'));
        }

        $request = [
            'user_id' => $user_id,
            'user_role_id' => $request->input('user_role_id', 0),
            'role_id' => $request->input('role_id', 0),
            'chain_id' => $request->input('chain_id', 0)
        ];

        $data = $this->userAdminService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_id = $request->input('user_id', -1);
        $this->userAdminValidator->setId($user_id);
        $this->userAdminValidator->with($request->all())->passesOrFail('update');

        $user_admin = $this->userAdminService->get($user_id);
        if (empty($user_admin)) {
            throw new ErrorException(__('系统用户不存在'));
        }

        $formatted_request = [
            'user_role_id' => $request->input('user_role_id', 0),
            'role_id' => $request->input('role_id', 0),
            'chain_id' => $request->input('chain_id', 0)
        ];

        $data = $this->userAdminService->edit($user_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_id = $request->input('user_id', -1);
        $user_admin = $this->userAdminService->get($user_id);
        // [修复 2026-09-23] 原代码少了空判断：user_id 不存在时 $user_admin 为 null，
        // 直接取 ['user_is_superadmin'] 会触发 "Trying to access array offset on null"，
        // 前端拿到的是 500 而不是可读提示。
        if (empty($user_admin)) {
            throw new ErrorException(__('系统用户不存在'));
        }
        if ($user_admin['user_is_superadmin']) {
            throw new ErrorException(__('系统内置，不可删除'));
        }
        $data = $this->userAdminService->remove($user_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-23] 补 `/manage/admin/userAdmin/removeBatch`（原先 404）。
     * 前端 views/admin/userAdmin/index.vue:265 传的是 `{user_id}` —— **数组**形态。
     *
     * ⚠️ 与单删一样必须把「超级管理员不可删除」这道闸逐个过一遍：
     *   批量里只要混进一个超管就整批拒绝，避免"点了删除、超管没了、其它也没删干净"的半成品状态。
     */
    public function removeBatch(Request $request)
    {
        $raw = $request->input('user_id', '');
        $ids = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        // 先整体校验，再执行删除 —— 避免删一半才报错
        $super_admin_ids = [];
        foreach ($ids as $user_id) {
            $user_admin = $this->userAdminService->get($user_id);
            if (empty($user_admin)) {
                throw new ErrorException(sprintf(__('系统用户 %d 不存在'), $user_id));
            }
            if ($user_admin['user_is_superadmin']) {
                $super_admin_ids[] = $user_id;
            }
        }
        if (!empty($super_admin_ids)) {
            throw new ErrorException(sprintf(__('系统内置账号不可删除：%s'), implode('、', $super_admin_ids)));
        }

        foreach ($ids as $user_id) {
            $this->userAdminService->remove($user_id);
        }

        return Respond::success(true, __('删除成功'));
    }

}
