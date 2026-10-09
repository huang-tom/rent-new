<?php
// +----------------------------------------------------------------------
// | [本地自研 2026-09-22] 社交圈子 - 动态评论（sns_story_comment）
// +----------------------------------------------------------------------

namespace Modules\Sns\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;

class StoryCommentController extends BaseController
{
    /**
     * 列表
     */
    public function list(Request $request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $size = max(1, min(200, (int)$request->input('size', 10)));

        $query = DB::table('sns_story_comment as c')
            ->leftJoin('account_user_info as u', 'u.user_id', '=', 'c.user_id')
            ->leftJoin('sns_story_base as s', 's.story_id', '=', 'c.story_id')
            ->select([
                'c.comment_id',
                'c.user_id',
                'c.story_id',
                'c.comment_content',
                'c.comment_state',
                'c.comment_is_show',
                'c.comment_like_count',
                'c.comment_time',
                'c.to_user_id',
                'u.user_nickname',
                'u.user_avatar',
                's.story_title',
            ]);

        $keyword = trim((string)$request->input('comment_content', ''));
        if ($keyword !== '') {
            $query->where('c.comment_content', 'like', '%' . $keyword . '%');
        }

        $story_id = (int)$request->input('story_id', 0);
        if ($story_id > 0) {
            $query->where('c.story_id', $story_id);
        }

        $user_id = (int)$request->input('user_id', 0);
        if ($user_id > 0) {
            $query->where('c.user_id', $user_id);
        }

        // 显示状态（0/1 均为合法值，需严格区分「未传」与传 0）
        $is_show = $request->input('comment_is_show', '');
        if ($is_show !== '' && $is_show !== null) {
            $query->where('c.comment_is_show', (int)$is_show);
        }

        $total = (clone $query)->count();

        $items = $query->orderBy('c.comment_id', 'desc')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                $row = (array)$row;
                if (!empty($row['comment_time'])) {
                    $row['comment_time_format'] = (string)$row['comment_time'];
                } else {
                    $row['comment_time_format'] = '';
                }
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
     * 修改状态：显示 / 隐藏
     */
    public function editState(Request $request)
    {
        $comment_id = (int)$request->input('comment_id', 0);
        if ($comment_id <= 0) {
            return Respond::error('缺少评论编号');
        }

        $exists = DB::table('sns_story_comment')->where('comment_id', $comment_id)->exists();
        if (!$exists) {
            return Respond::error('评论不存在');
        }

        if (!$request->has('comment_is_show')) {
            return Respond::error('没有需要更新的字段');
        }

        DB::table('sns_story_comment')
            ->where('comment_id', $comment_id)
            ->update(['comment_is_show' => (int)$request->boolean('comment_is_show') ? 1 : 0]);

        return Respond::success([], '操作成功');
    }

    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $comment_id = (int)$request->input('comment_id', 0);
        if ($comment_id <= 0) {
            return Respond::error('缺少评论编号');
        }

        $exists = DB::table('sns_story_comment')->where('comment_id', $comment_id)->exists();
        if (!$exists) {
            return Respond::error('评论不存在');
        }

        DB::beginTransaction();
        try {
            // 同步扣减动态的评论数，保持计数一致
            $story_id = (int)DB::table('sns_story_comment')
                ->where('comment_id', $comment_id)
                ->value('story_id');

            DB::table('sns_story_comment')->where('comment_id', $comment_id)->delete();

            if ($story_id > 0) {
                DB::table('sns_story_base')
                    ->where('story_id', $story_id)
                    ->where('story_comment_count', '>', 0)
                    ->decrement('story_comment_count');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return Respond::error('删除失败：' . $e->getMessage());
        }

        return Respond::success([], '删除成功');
    }
}
