<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductCommentReplyCriteria;
use Modules\Pt\Services\ProductCommentReplyService;

class ProductCommentReplyController extends BaseController
{
    private $productCommentReplyService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductCommentReplyService $productCommentReplyService)
    {
        $this->productCommentReplyService = $productCommentReplyService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productCommentReplyService->list($request, new ProductCommentReplyCriteria($request));

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $comment_reply_id = $request->get('comment_reply_id', -1);
        $state_data = [];
        if ($request->has('comment_reply_enable')) {
            $state_data['comment_reply_enable'] = $request->boolean('comment_reply_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $data = $this->productCommentReplyService->edit($comment_reply_id, $state_data);
            return Respond::success($data);
        } else {
            throw new ErrorException(__('无修改数据'));
        }
    }


    /**
     *  添加回复
     */
    public function add(Request $request)
    {
        $data = $this->productCommentReplyService->addCommentReply($request);

        return Respond::success($data);
    }

}
