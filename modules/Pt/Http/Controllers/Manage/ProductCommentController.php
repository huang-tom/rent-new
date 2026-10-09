<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductCommentCriteria;
use Modules\Pt\Services\ProductCommentService;

class ProductCommentController extends BaseController
{
    private $productCommentService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductCommentService $productCommentService)
    {
        $this->productCommentService = $productCommentService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productCommentService->list($request, new ProductCommentCriteria($request));

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $comment_id = $request->get('comment_id', -1);
        $state_data = [];
        if ($request->has('comment_enable')) {
            $state_data['comment_enable'] = $request->boolean('comment_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $data = $this->productCommentService->edit($comment_id, $state_data);
            return Respond::success($data);
        } else {
            throw new ErrorException('无修改数据');
        }
    }


    public function remove(Request $request)
    {
        $comment_id = $request->get('comment_id', -1);
        $res = $this->productCommentService->removeComment($comment_id);

        return Respond::success($res);
    }

}
