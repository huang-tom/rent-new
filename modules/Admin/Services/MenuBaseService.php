<?php

namespace Modules\Admin\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Admin\Repositories\Contracts\MenuBaseRepository;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;
use Modules\Admin\Repositories\Models\MenuBase;
use Modules\Admin\Repositories\Models\UserRole;

class MenuBaseService extends BaseService
{
    private $userAdminRepository;
    private $userRoleRepository;

    public function __construct(
        MenuBaseRepository  $menuBaseRepository,
        UserAdminRepository $userAdminRepository,
        UserRoleRepository  $userRoleRepository
    )
    {
        $this->repository = $menuBaseRepository;
        $this->userAdminRepository = $userAdminRepository;
        $this->userRoleRepository = $userRoleRepository;
    }

    public function treeMenus($condition = [])
    {
        if (isset($condition['menu_type'])) {
            $user_id = auth()->payload()->get('user_id');
            $admin_user = $this->userAdminRepository->getOne($user_id);
            if (!empty($admin_user)) {
                $menu_ids = [-1];
                //todo 获取用户的角色权限菜单
                $user_role_id = $admin_user['user_role_id'];
                $user_role_menu = $this->userRoleRepository->getOne($user_role_id);
                if (!empty($user_role_menu)) {
                    $menu_ids = explode(',', $user_role_menu['menu_ids']);
                }

                $condition[] = ['menu_id', 'IN', $menu_ids];
            }
        }

        $data = $this->repository->find($condition, ['menu_sort' => 'ASC']);
        $data = ArrayToTree($data, 0, 'children', 'menu_');

        return $data;
    }


    /**
     * 删除菜单（含角色授权位的清理）
     *
     * [新增 2026-09-23] 前端「系统管理 → 菜单管理」的删除按钮一直在调
     * POST /manage/admin/menu/remove，后端**路由和控制器方法都没有** → 404。
     *
     * ⚠️ 这是 RBAC 相关操作，删之前必须过三道闸（缺一个都会把权限体系搞坏）：
     *   1) 有子菜单 → 拒绝。否则子菜单变成孤儿节点，树里再也显示不出来，
     *      但它仍然是某些角色 menu_ids 里的有效授权，形成"看不见却有权"的幽灵权限。
     *   2) menu_buildin=1 → 拒绝。内置菜单是系统运行必需的（如运营首页），
     *      删掉会让某个角色登录后首页空白。
     *   3) 删除成功后，必须把该 menu_id 从**所有角色**的 menu_ids 里摘掉 ——
     *      留着悬空 ID 虽然 getUserPermissions() 会因为查不到而忽略，
     *      但下次在菜单管理里新增出同 ID 的记录时会"凭空恢复"授权。
     *      （本项目 id 是自增的，虽不会复用，但显式清理才不依赖这个假设。）
     *
     * menu_ids 是逗号串，清理时不能简单 str_replace：搜 "4373" 会把 "14373" 也改掉。
     * 所以按逗号 split → 逐个比对 → 重新拼。
     *
     * @param int $menu_id
     * @return bool
     * @throws ErrorException
     */
    public function removeMenu(int $menu_id = 0)
    {
        if (!$menu_id) {
            throw new ErrorException(__('菜单编号不能为空'));
        }

        $menu = MenuBase::find($menu_id);
        if (empty($menu)) {
            throw new ErrorException(__('菜单不存在'));
        }

        // 闸 1：有子菜单
        $children = MenuBase::where('menu_parent_id', $menu_id)->count();
        if ($children > 0) {
            throw new ErrorException(sprintf(__('该菜单下还有 %d 个子菜单，请先删除子菜单'), $children));
        }

        // 闸 2：内置菜单
        if ($menu->menu_buildin) {
            throw new ErrorException(__('系统内置菜单不可删除'));
        }

        DB::beginTransaction();
        try {
            MenuBase::where('menu_id', $menu_id)->delete();

            // 闸 3：清掉所有角色里对这个菜单的授权位
            foreach (UserRole::query()->get() as $role) {
                $ids = array_filter(array_map('trim', explode(',', (string)$role->menu_ids)),
                    static fn($v) => $v !== '');
                if (!in_array((string)$menu_id, $ids, true)) {
                    continue;
                }
                $ids = array_values(array_diff($ids, [(string)$menu_id]));
                UserRole::where('user_role_id', $role->user_role_id)
                    ->update(['menu_ids' => implode(',', $ids)]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除菜单失败: ') . $e->getMessage());
        }

        return true;
    }

}
