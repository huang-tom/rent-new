<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\NewProductCommentCriteria;
use Modules\Pt\Repositories\Validators\NewProductCommentValidator;
use Modules\Pt\Services\NewProductCommentService;

class NewProductCommentController extends BaseController
{
    private $newProductCommentService;
    private $newProductCommentValidator;

    public function __construct(
        NewProductCommentService   $newProductCommentService,
        NewProductCommentValidator $newProductCommentValidator
    )
    {
        $this->newProductCommentService = $newProductCommentService;
        $this->newProductCommentValidator = $newProductCommentValidator;
    }

    /**
     * 列表
     * keyword / comment_scores / score_type(5|4|3) / audit_status(0待审1通过2驳回) / product_id
     */
    public function list(Request $request)
    {
        $data = $this->newProductCommentService->list($request, new NewProductCommentCriteria($request));

        return Respond::success($data);
    }

    /**
     * 统计卡片
     */
    public function statistics(Request $request)
    {
        $data = $this->newProductCommentService->getStatistics();

        return Respond::success($data);
    }

    public function formatRequest(Request $request)
    {
        $data = [
            'product_id' => (int)$request->input('product_id', 0),
            'product_name' => $request->input('product_name', ''),
            'user_name' => $request->input('user_name', ''),
            'comment_scores' => (int)$request->input('comment_scores', 5),
            'comment_content' => $request->input('comment_content', ''),
            'comment_image' => $request->input('comment_image', ''),
            'comment_time' => $request->input('comment_time', null),
        ];
        // 仅显式传入时才改审核状态，避免编辑覆盖
        if ($request->has('audit_status') && $request->input('audit_status') !== '') {
            $data['audit_status'] = (int)$request->input('audit_status');
        }

        return $data;
    }

    /**
     * 新增主评论
     */
    public function add(Request $request)
    {
        $this->newProductCommentValidator->with($request->all())->passesOrFail('create');
        $data = $this->newProductCommentService->addComment($this->formatRequest($request));

        return Respond::success($data);
    }

    /**
     * 编辑主评论
     */
    public function edit(Request $request)
    {
        $comment_id = (int)$request->get('comment_id', -1);
        $this->newProductCommentValidator->setId($comment_id);
        $this->newProductCommentValidator->with($request->all())->passesOrFail('update');
        $data = $this->newProductCommentService->editComment($comment_id, $this->formatRequest($request));

        return Respond::success($data);
    }

    /**
     * 审核：audit_status 0待审 / 1通过 / 2驳回
     */
    public function audit(Request $request)
    {
        $comment_id = (int)$request->get('comment_id', -1);
        $audit_status = (int)$request->input('audit_status', 0);
        $data = $this->newProductCommentService->audit($comment_id, $audit_status);

        return Respond::success($data);
    }

    /**
     * 软删除主评论
     */
    public function remove(Request $request)
    {
        $comment_id = (int)$request->get('comment_id', -1);
        $data = $this->newProductCommentService->removeComment($comment_id);

        return Respond::success($data);
    }

    /**
     * 添加回复
     */
    public function addReply(Request $request)
    {
        $comment_id = (int)$request->input('comment_id', 0);
        $reply_content = $request->input('reply_content', '');
        $reply_time = $request->input('reply_time', null);
        $data = $this->newProductCommentService->addReply($comment_id, $reply_content, $reply_time);

        return Respond::success($data);
    }

    /**
     * 编辑回复
     */
    public function editReply(Request $request)
    {
        $reply_id = (int)$request->input('reply_id', 0);
        $reply_content = $request->input('reply_content', '');
        $reply_time = $request->input('reply_time', null);
        $data = $this->newProductCommentService->editReply($reply_id, $reply_content, $reply_time);

        return Respond::success($data);
    }

    /**
     * 软删除回复
     */
    public function removeReply(Request $request)
    {
        $reply_id = (int)$request->input('reply_id', 0);
        $data = $this->newProductCommentService->removeReply($reply_id);

        return Respond::success($data);
    }
}
