<?php

namespace Modules\Admin\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Admin\Services\MenuBaseService;

class MenuBaseController extends BaseController
{
    private $menuBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MenuBaseService $menuBaseService)
    {
        $this->menuBaseService = $menuBaseService;
    }

    /*
     * 获取树形菜单
     */
    public function tree(Request $request)
    {
        $condition = ['menu_role' => 1];
        if ($request['type'] != 2) {
            $condition['menu_type'] = 1;
            $condition['menu_enable'] = 1;
            $condition['menu_hidden'] = 0;
        }
        if ($menu_title = $request->input('menu_title', '')) {
            $condition['menu_title'] = $menu_title;
        }
        $data = $this->menuBaseService->treeMenus($condition);

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $menu_id = $request->get('menu_id');
        $state_data = [];

        if ($request->has('menu_close')) {
            $state_data['menu_close'] = $request->boolean('menu_close');
        }

        if ($request->has('menu_hidden')) {
            $state_data['menu_hidden'] = $request->boolean('menu_hidden');
        }

        if ($request->has('menu_enable')) {
            $state_data['menu_enable'] = $request->boolean('menu_enable');
        }

        if ($request->has('menu_dot')) {
            $state_data['menu_dot'] = $request->boolean('menu_dot');
        }

        if ($request->has('menu_buildin')) {
            $state_data['menu_buildin'] = $request->boolean('menu_buildin');
        }

        // 更新状态
        if ($menu_id && !empty($state_data)) {
            $result = $this->menuBaseService->edit($menu_id, $state_data);
            if ($result) {
                // [修复 2026-09-23] 原为 `return true;` —— 控制器直接返回裸 bool，
                // Lumen 会把它序列化成响应体 `1`（不是 Respond 包装的 {status:200,...}），
                // 前端 `if (200 == status)` 永远不成立，于是"开关明明改了却提示失败"。
                return Respond::success(true, __('操作成功'));
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($state_data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        return [
            'menu_parent_id' => $request->input('menu_parent_id', 0),   // 项目标题
            'menu_title' => $request->input('menu_title', ''),
            'menu_url' => $request->input('menu_url', ''),
            'menu_name' => $request->input('menu_name', ''),
            'menu_path' => $request->input('menu_path', ''),
            'menu_component' => $request->input('menu_component', ''),
            'menu_redirect' => $request->input('menu_redirect', ''),
            'menu_class' => $request->input('menu_class', ''),
            'menu_icon' => $request->input('menu_icon', ''),
            'menu_bubble' => $request->input('menu_bubble', ''),
            'menu_sort' => $request->boolean('menu_sort', false),
            'menu_type' => $request->input('menu_type', ''),
            'menu_note' => $request->input('menu_note', ''),
            'menu_func' => $request->input('menu_func', ''),
            'menu_role' => $request->input('menu_role', ''),
            'menu_param' => $request->input('menu_param', ''),
            'menu_permission' => $request->input('menu_permission', ''),

            'menu_close' => $request->boolean('menu_close', false),
            'menu_hidden' => $request->boolean('menu_hidden', false),
            'menu_enable' => $request->boolean('menu_enable', false),
            'menu_dot' => $request->boolean('menu_dot', false),
            'menu_buildin' => $request->boolean('menu_buildin', false)
        ];
    }

    public function edit(Request $request)
    {
        $menu_id = $request->input('menu_id', -1);
        $formatted_request = $this->formatRequest($request);
        $data = $this->menuBaseService->edit($menu_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 新增菜单
     *
     * [新增 2026-09-23] 路由 `/manage/admin/menu/add` 早在 Admin/Routes/web.php:18 就声明了，
     * 但 MenuBaseController 里从来没有 add 方法 —— Lumen 对"方法不存在"抛 NotFoundHttpException，
     * 实测就是 **404**（不是 500）。所以「菜单管理 → 添加」一直是坏的，
     * 而契约校验（只比前端 URL 与已声明路由）**看不见**这类问题，
     * 是 route_method_audit.py 专门扫出来的 11 条之一。
     */
    public function add(Request $request)
    {
        $parent_id = (int)$request->input('menu_parent_id', 0);
        $title = trim((string)$request->input('menu_title', ''));
        if ($title === '') {
            return Respond::error(__('菜单名称不能为空'));
        }

        // 父菜单必须真实存在，否则新菜单会挂在 0 之下成为顶级菜单，
        // 而前端是拿不到这个"隐式顶级"节点的，等于凭空多出一个看不见的菜单。
        if ($parent_id > 0) {
            $parent = $this->menuBaseService->get($parent_id);
            if (empty($parent)) {
                return Respond::error(__('上级菜单不存在'));
            }
        }

        $add_row = $this->formatRequest($request);
        $add_row['menu_time'] = getDateTime();

        $data = $this->menuBaseService->add($add_row);

        return Respond::success($data);
    }


    /**
     * 删除菜单
     *
     * [新增 2026-09-23] 补 `/manage/admin/menu/remove`（原先连路由都没有）。
     *
     * ⚠️ 前端**两种调用形态**，必须都支持（踩过的坑，见技能 §16.3）：
     *   单个删除：index.vue:269  doRemove({menu_id: row.menu_id})          → 标量
     *   批量删除：index.vue:284  doRemove({menu_id}) 其中 menu_id 是数组   → 数组
     *   所以这里统一收口成数组再逐个删；只认标量的话批量删除会静默失效
     *   （intval([]) 得到 0，然后报"菜单编号不能为空"，用户看着像没反应）。
     */
    public function remove(Request $request)
    {
        $raw = $request->input('menu_id', '');
        $ids = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return Respond::error(__('请选择要删除的菜单'));
        }

        foreach ($ids as $menu_id) {
            $this->menuBaseService->removeMenu($menu_id);
        }

        return Respond::success(true, __('删除成功'));
    }

}
