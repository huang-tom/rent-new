<?php
// +----------------------------------------------------------------------
// | [本地自研 2026-09-22] 社交圈子 - 圈子动态（sns_story_base）
// +----------------------------------------------------------------------
// | 说明：厂商未交付该模块的后端实现，但前端 url.config.js 已定义
// |       /manage/sns/storyBase/** 接口契约，故按契约补齐。
// |       刻意不依赖 core 私有包的混淆基类（BaseRepository/BaseService），
// |       直接用 DB facade + 手写分页，行为完全可控。
// +----------------------------------------------------------------------

namespace Modules\Sns\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class StoryBaseController extends BaseController
{
    /**
     * 动态状态：0=待审核 1=已通过 2=已拒绝（与 story_status 语义一致）
     */
    const STATE_PENDING = 0;
    const STATE_APPROVED = 1;
    const STATE_REJECTED = 2;

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $size = max(1, min(200, (int)$request->input('size', 10)));

        $query = DB::table('sns_story_base as s')
            ->leftJoin('account_user_info as u', 'u.user_id', '=', 's.user_id')
            ->select([
                's.story_id',
                's.user_id',
                's.story_title',
                's.story_content',
                's.story_file',
                's.story_video',
                's.story_type',
                's.story_time',
                's.story_status',
                's.story_enable',
                's.story_privacy',
                's.story_is_top',
                's.story_like_count',
                's.story_comment_count',
                's.story_forward_count',
                's.story_collection_count',
                's.story_brower_count',
                's.story_category_id',
                's.story_tags',
                's.item_id',
                's.product_id',
                'u.user_nickname',
                'u.user_avatar',
            ]);

        // 关键字：标题 / 内容
        $keyword = trim((string)$request->input('story_title', ''));
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('s.story_title', 'like', '%' . $keyword . '%')
                    ->orWhere('s.story_content', 'like', '%' . $keyword . '%');
            });
        }

        // 发布人
        $user_id = (int)$request->input('user_id', 0);
        if ($user_id > 0) {
            $query->where('s.user_id', $user_id);
        }

        // 审核状态（注意 0 是合法值，必须用 !== '' 判断）
        $status = $request->input('story_status', '');
        if ($status !== '' && $status !== null) {
            $query->where('s.story_status', (int)$status);
        }

        // 分类
        $category_id = (int)$request->input('story_category_id', 0);
        if ($category_id > 0) {
            $query->where('s.story_category_id', $category_id);
        }

        $total = (clone $query)->count();

        $rows = $query->orderBy('s.story_is_top', 'desc')
            ->orderBy('s.story_id', 'desc')
            ->forPage($page, $size)
            ->get()
            ->all();

        // 类别名称映射（避免逐行查询）
        $category_names = $this->categoryNameMap();

        $items = [];
        foreach ($rows as $row) {
            $row = (array)$row;

            // 图片：story_file 为 JSON 数组字符串
            $files = json_decode((string)$row['story_file'], true);
            if (!is_array($files)) {
                $files = $row['story_file'] !== '' ? [$row['story_file']] : [];
            }
            $row['story_images'] = array_values(array_slice($files, 0, 3));
            $row['story_image_count'] = count($files);

            $row['story_time_format'] = $row['story_time'] > 0
                ? date('Y-m-d H:i:s', (int)$row['story_time'])
                : '';

            $row['story_category_name'] = $category_names[(int)$row['story_category_id']] ?? '';

            $items[] = $row;
        }

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
     * 修改状态：审核通过/拒绝、置顶、显示隐藏
     */
    public function editState(Request $request)
    {
        $story_id = (int)$request->input('story_id', 0);
        if ($story_id <= 0) {
            return Respond::error('缺少动态编号');
        }

        $exists = DB::table('sns_story_base')->where('story_id', $story_id)->exists();
        if (!$exists) {
            return Respond::error('动态不存在');
        }

        $update = [];

        if ($request->has('story_status')) {
            $update['story_status'] = (int)$request->input('story_status');
        }
        if ($request->has('story_is_top')) {
            $update['story_is_top'] = (int)$request->boolean('story_is_top') ? 1 : 0;
        }
        if ($request->has('story_enable')) {
            $update['story_enable'] = (int)$request->boolean('story_enable') ? 1 : 0;
        }

        if (empty($update)) {
            return Respond::error('没有需要更新的字段');
        }

        DB::table('sns_story_base')->where('story_id', $story_id)->update($update);

        return Respond::success([], '操作成功');
    }

    /**
     * 删除（会级联清理该动态的评论）
     */
    public function remove(Request $request)
    {
        $story_id = (int)$request->input('story_id', 0);
        if ($story_id <= 0) {
            return Respond::error('缺少动态编号');
        }

        $exists = DB::table('sns_story_base')->where('story_id', $story_id)->exists();
        if (!$exists) {
            return Respond::error('动态不存在');
        }

        DB::beginTransaction();
        try {
            DB::table('sns_story_comment')->where('story_id', $story_id)->delete();
            DB::table('sns_story_base')->where('story_id', $story_id)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return Respond::error('删除失败：' . $e->getMessage());
        }

        return Respond::success([], '删除成功');
    }

    /**
     * 分类 id => 名称
     */
    private function categoryNameMap(): array
    {
        $rows = DB::table('sns_story_category')
            ->select(['story_category_id', 'story_category_name'])
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row->story_category_id] = $row->story_category_name;
        }
        return $map;
    }
}
