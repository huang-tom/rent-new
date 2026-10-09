<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\NewProductCommentReplyRepository;
use Modules\Pt\Repositories\Contracts\NewProductCommentRepository;

/**
 * Class NewProductCommentService.
 *
 * @package Modules\Pt\Services
 */
class NewProductCommentService extends BaseService
{
    /** 待审核 */
    const AUDIT_PENDING = 0;
    /** 审核通过 */
    const AUDIT_PASSED = 1;
    /** 已驳回 */
    const AUDIT_REJECTED = 2;

    /** 评分筛选-五星 */
    const SCORE_TYPE_5 = 5;
    /** 评分筛选-四星 */
    const SCORE_TYPE_4 = 4;
    /** 评分筛选-三星及以下 */
    const SCORE_TYPE_3_BELOW = 3;

    private $newProductCommentReplyRepository;

    public function __construct(
        NewProductCommentRepository      $newProductCommentRepository,
        NewProductCommentReplyRepository $newProductCommentReplyRepository
    )
    {
        $this->repository = $newProductCommentRepository;
        $this->newProductCommentReplyRepository = $newProductCommentReplyRepository;
    }

    /**
     * 列表（附带回复）
     */
    public function list($request, $criteria)
    {
        $data = parent::list($request, $criteria);
        if (empty($data['data'])) {
            return $data;
        }

        $comment_ids = array_column($data['data'], 'comment_id');
        $reply_map = [];
        if ($comment_ids) {
            $replies = $this->newProductCommentReplyRepository->find([
                ['comment_id', 'IN', $comment_ids],
                ['is_deleted', '=', 0],
            ], [
                'reply_id' => 'ASC',
            ]) ?: [];
            foreach ($replies as $reply) {
                $cid = (int)$reply['comment_id'];
                if (!isset($reply_map[$cid])) {
                    $reply_map[$cid] = [];
                }
                $reply_map[$cid][] = $reply;
            }
        }

        foreach ($data['data'] as $k => $row) {
            $cid = (int)$row['comment_id'];
            $images = [];
            if (!empty($row['comment_image'])) {
                $images = array_values(array_filter(explode(',', $row['comment_image'])));
            }
            $data['data'][$k]['comment_images'] = $images;
            $data['data'][$k]['audit_status_text'] = $this->formatAuditText($row['audit_status'] ?? 0);
            $data['data'][$k]['reply_list'] = $reply_map[$cid] ?? [];
            $data['data'][$k]['reply_num'] = isset($reply_map[$cid]) ? count($reply_map[$cid]) : 0;
        }

        return $data;
    }

    /**
     * 顶部统计
     */
    public function getStatistics()
    {
        $base = [['is_deleted', '=', 0]];

        $total = (int)$this->repository->getNum($base);
        $pending = (int)$this->repository->getNum(array_merge($base, [['audit_status', '=', self::AUDIT_PENDING]]));
        $passed = (int)$this->repository->getNum(array_merge($base, [['audit_status', '=', self::AUDIT_PASSED]]));
        $rejected = (int)$this->repository->getNum(array_merge($base, [['audit_status', '=', self::AUDIT_REJECTED]]));

        $avg = 0;
        $avg_row = DB::table('pt_new_product_comment')
            ->where('is_deleted', 0)
            ->selectRaw('AVG(comment_scores) AS avg_score')
            ->first();
        if ($avg_row && $avg_row->avg_score !== null) {
            $avg = round((float)$avg_row->avg_score, 1);
        }

        $replied = (int)DB::table('pt_new_product_comment as c')
            ->where('c.is_deleted', 0)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pt_new_product_comment_reply as r')
                    ->whereRaw('r.comment_id = c.comment_id')
                    ->where('r.is_deleted', 0);
            })
            ->count('c.comment_id');

        return [
            'total_count' => $total,
            'pending_count' => $pending,
            'passed_count' => $passed,
            'rejected_count' => $rejected,
            'avg_score' => $avg,
            'replied_count' => $replied,
        ];
    }

    /**
     * 新增主评论
     */
    public function addComment(array $data)
    {
        $now = getDateTime();
        $row = $this->formatCommentData($data);
        $row['add_time'] = $now;
        $row['update_time'] = $now;
        $row['is_deleted'] = 0;
        if (empty($row['comment_time'])) {
            $row['comment_time'] = $now;
        }
        if (!isset($data['audit_status'])) {
            $row['audit_status'] = self::AUDIT_PENDING;
        }

        return $this->repository->add($row);
    }

    /**
     * 编辑主评论
     */
    public function editComment($comment_id, array $data)
    {
        $this->getExistComment($comment_id);
        $row = $this->formatCommentData($data);
        $row['update_time'] = getDateTime();
        if (isset($data['audit_status'])) {
            $row['audit_status'] = (int)$data['audit_status'];
        }
        if (array_key_exists('comment_time', $data) && $data['comment_time'] !== '' && $data['comment_time'] !== null) {
            $row['comment_time'] = $this->parseTime($data['comment_time']);
        }

        return $this->repository->edit($comment_id, $row);
    }

    /**
     * 审核：通过/驳回/待审核
     */
    public function audit($comment_id, $audit_status)
    {
        $this->getExistComment($comment_id);
        $audit_status = (int)$audit_status;
        if (!in_array($audit_status, [self::AUDIT_PENDING, self::AUDIT_PASSED, self::AUDIT_REJECTED], true)) {
            throw new ErrorException('审核状态不正确');
        }

        $result = $this->repository->edit($comment_id, [
            'audit_status' => $audit_status,
            'update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('审核失败');
        }

        return true;
    }

    /**
     * 软删除主评论（同步软删回复）
     */
    public function removeComment($comment_id)
    {
        $this->getExistComment($comment_id);

        DB::beginTransaction();
        try {
            $result = $this->repository->edit($comment_id, [
                'is_deleted' => 1,
                'update_time' => getDateTime(),
            ]);
            if (!$result) {
                throw new ErrorException('删除失败');
            }

            $replies = $this->newProductCommentReplyRepository->find([
                'comment_id' => $comment_id,
                'is_deleted' => 0,
            ]) ?: [];
            $now = getDateTime();
            foreach ($replies as $reply) {
                $this->newProductCommentReplyRepository->edit($reply['reply_id'], [
                    'is_deleted' => 1,
                    'update_time' => $now,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '删除失败');
        }
    }

    /**
     * 添加回复
     */
    public function addReply($comment_id, $reply_content, $reply_time = null)
    {
        $this->getExistComment($comment_id);
        if ($reply_content === '' || $reply_content === null) {
            throw new ErrorException('回复内容不能为空');
        }

        $now = getDateTime();
        $time = $reply_time !== null && $reply_time !== '' ? $this->parseTime($reply_time) : $now;

        return $this->newProductCommentReplyRepository->add([
            'comment_id' => (int)$comment_id,
            'reply_content' => $reply_content,
            'reply_time' => $time,
            'is_deleted' => 0,
            'add_time' => $now,
            'update_time' => $now,
        ]);
    }

    /**
     * 编辑回复
     */
    public function editReply($reply_id, $reply_content, $reply_time = null)
    {
        $reply = $this->getExistReply($reply_id);
        if ($reply_content === '' || $reply_content === null) {
            throw new ErrorException('回复内容不能为空');
        }

        $row = [
            'reply_content' => $reply_content,
            'update_time' => getDateTime(),
        ];
        if ($reply_time !== null && $reply_time !== '') {
            $row['reply_time'] = $this->parseTime($reply_time);
        }

        return $this->newProductCommentReplyRepository->edit($reply_id, $row);
    }

    /**
     * 软删除回复
     */
    public function removeReply($reply_id)
    {
        $this->getExistReply($reply_id);
        $result = $this->newProductCommentReplyRepository->edit($reply_id, [
            'is_deleted' => 1,
            'update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('删除失败');
        }

        return true;
    }

    private function formatCommentData(array $data)
    {
        $images = $data['comment_image'] ?? '';
        if (is_array($images)) {
            $images = implode(',', array_filter($images));
        }

        $row = [
            'product_id' => (int)($data['product_id'] ?? 0),
            'product_name' => $data['product_name'] ?? '',
            'user_name' => $data['user_name'] ?? '',
            'comment_scores' => (int)($data['comment_scores'] ?? 5),
            'comment_content' => $data['comment_content'] ?? '',
            'comment_image' => (string)$images,
        ];

        if (isset($data['audit_status']) && $data['audit_status'] !== '') {
            $row['audit_status'] = (int)$data['audit_status'];
        }

        if (array_key_exists('comment_time', $data) && $data['comment_time'] !== '' && $data['comment_time'] !== null) {
            $row['comment_time'] = $this->parseTime($data['comment_time']);
        }

        if ($row['comment_scores'] < 1 || $row['comment_scores'] > 5) {
            throw new ErrorException('评分须在1-5之间');
        }

        return $row;
    }

    /**
     * 支持时间戳（秒/毫秒）或日期字符串，统一存 Y-m-d H:i:s
     */
    private function parseTime($value)
    {
        if (is_numeric($value)) {
            $ts = (int)$value;
            // 约 13 位为毫秒，转秒
            if ($ts >= 10000000000) {
                $ts = (int)floor($ts / 1000);
            }
            return date('Y-m-d H:i:s', $ts);
        }
        $raw = trim((string)$value);
        $ts = strtotime($raw);
        if ($ts === false) {
            throw new ErrorException('时间格式不正确');
        }
        // 仅日期时补 00:00:00，已是完整时间则按解析结果
        return date('Y-m-d H:i:s', $ts);
    }

    private function getExistComment($comment_id)
    {
        $comment = $this->repository->getOne($comment_id);
        if (!$comment || !empty($comment['is_deleted'])) {
            throw new ErrorException('评论不存在');
        }

        return $comment;
    }

    private function getExistReply($reply_id)
    {
        $reply = $this->newProductCommentReplyRepository->getOne($reply_id);
        if (!$reply || !empty($reply['is_deleted'])) {
            throw new ErrorException('回复不存在');
        }

        return $reply;
    }

    private function formatAuditText($status)
    {
        $map = [
            self::AUDIT_PENDING => '待审核',
            self::AUDIT_PASSED => '审核通过',
            self::AUDIT_REJECTED => '已驳回',
        ];

        return $map[(int)$status] ?? '';
    }
}
