<?php
// +----------------------------------------------------------------------
// | [本地自研 2026-09-22] 社交圈子 - 圈子分类（sns_story_category）
// +----------------------------------------------------------------------

namespace Modules\Sns\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class StoryCategoryController extends BaseController
{
    /**
     * 列表
     */
    public function list(Request $request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $size = max(1, min(500, (int)$request->input('size', 50)));

        $query = DB::table('sns_story_category');

        $keyword = trim((string)$request->input('story_category_name', ''));
        if ($keyword !== '') {
            $query->where('story_category_name', 'like', '%' . $keyword . '%');
        }

        $total = (clone $query)->count();

        $items = $query->orderBy('story_category_order', 'asc')
            ->orderBy('story_category_id', 'asc')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                $row = (array)$row;
                // 该表无 enable/status 字段，前端统一用 buildin 展示「系统内置」标识
                $row['story_category_enable'] = $row['story_category_buildin'] ? 0 : 1;
                return $row;
            })
            ->all();

        $payload = [
            'data'         => $items,
            'current_page' => $page,
            'total'        => $total,
            'limit'        => $size,
            'last_page'    => $size > 0 ? (int)ceil($total / $size) : 0,
        ];

        return Respond::success(Respond::transformPageData($payload));
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $name = trim((string)$request->input('story_category_name', ''));
        if ($name === '') {
            return Respond::error('请填写分类名称');
        }

        $dup = DB::table('sns_story_category')
            ->where('story_category_name', $name)
            ->exists();
        if ($dup) {
            return Respond::error('该分类名称已存在');
        }

        $id = DB::table('sns_story_category')->insertGetId([
            'story_category_name'      => $name,
            'story_category_parent_id' => (int)$request->input('story_category_parent_id', 0),
            'story_category_logo'      => (string)$request->input('story_category_logo', ''),
            'story_category_keywords'  => (string)$request->input('story_category_keywords', ''),
            'story_category_desc'      => (string)$request->input('story_category_desc', ''),
            'story_category_count'     => 0,
            'story_category_template'  => '',
            'story_category_alias'     => (string)$request->input('story_category_alias', ''),
            'story_category_order'     => (int)$request->input('story_category_order', 50),
            'story_category_buildin'   => 0,
            'story_category_link'      => '',
            'story_open_mode'          => 0,
        ]);

        return Respond::success(['story_category_id' => $id], '添加成功');
    }

    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $id = (int)$request->input('story_category_id', 0);
        if ($id <= 0) {
            return Respond::error('缺少分类编号');
        }

        $exists = DB::table('sns_story_category')->where('story_category_id', $id)->exists();
        if (!$exists) {
            return Respond::error('分类不存在');
        }

        $update = [];
        if ($request->has('story_category_name')) {
            $name = trim((string)$request->input('story_category_name', ''));
            if ($name === '') {
                return Respond::error('请填写分类名称');
            }
            $dup = DB::table('sns_story_category')
                ->where('story_category_name', $name)
                ->where('story_category_id', '<>', $id)
                ->exists();
            if ($dup) {
                return Respond::error('该分类名称已存在');
            }
            $update['story_category_name'] = $name;
        }
        if ($request->has('story_category_logo')) {
            $update['story_category_logo'] = (string)$request->input('story_category_logo', '');
        }
        if ($request->has('story_category_keywords')) {
            $update['story_category_keywords'] = (string)$request->input('story_category_keywords', '');
        }
        if ($request->has('story_category_desc')) {
            $update['story_category_desc'] = (string)$request->input('story_category_desc', '');
        }
        if ($request->has('story_category_alias')) {
            $update['story_category_alias'] = (string)$request->input('story_category_alias', '');
        }
        if ($request->has('story_category_order')) {
            $update['story_category_order'] = (int)$request->input('story_category_order', 50);
        }

        if (empty($update)) {
            return Respond::error('没有需要更新的字段');
        }

        DB::table('sns_story_category')->where('story_category_id', $id)->update($update);

        return Respond::success([], '修改成功');
    }

    /**
     * 修改状态
     * 注意：该表本身没有启用/禁用字段，故此接口实现为「修改排序」，
     * 保留它是为了兼容前端已有的 url.config.js 契约。
     */
    public function editState(Request $request)
    {
        $id = (int)$request->input('story_category_id', 0);
        if ($id <= 0) {
            return Respond::error('缺少分类编号');
        }

        $exists = DB::table('sns_story_category')->where('story_category_id', $id)->exists();
        if (!$exists) {
            return Respond::error('分类不存在');
        }

        DB::table('sns_story_category')
            ->where('story_category_id', $id)
            ->update(['story_category_order' => (int)$request->input('story_category_order', 50)]);

        return Respond::success([], '操作成功');
    }

    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $id = (int)$request->input('story_category_id', 0);
        if ($id <= 0) {
            return Respond::error('缺少分类编号');
        }

        $row = DB::table('sns_story_category')->where('story_category_id', $id)->first();
        if (!$row) {
            return Respond::error('分类不存在');
        }
        if ((int)$row->story_category_buildin === 1) {
            return Respond::error('系统内置分类不允许删除');
        }

        // 分类下还有动态时禁止删除，避免数据悬空
        $used = DB::table('sns_story_base')->where('story_category_id', $id)->exists();
        if ($used) {
            return Respond::error('该分类下仍有动态，请先迁移或删除后再试');
        }

        // 存在子分类时同样禁止删除
        $has_children = DB::table('sns_story_category')
            ->where('story_category_parent_id', $id)
            ->exists();
        if ($has_children) {
            return Respond::error('请先删除该分类下的子分类');
        }

        DB::table('sns_story_category')->where('story_category_id', $id)->delete();

        return Respond::success([], '删除成功');
    }
}
