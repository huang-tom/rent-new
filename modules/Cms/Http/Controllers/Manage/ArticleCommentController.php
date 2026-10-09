<?php

namespace Modules\Cms\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Cms\Repositories\Criteria\ArticleCommentCriteria;
use Modules\Cms\Services\ArticleCommentService;

class ArticleCommentController extends BaseController
{
    private $articleCommentService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleCommentService $articleCommentService)
    {
        $this->articleCommentService = $articleCommentService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->articleCommentService->list($request, new ArticleCommentCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'article_id' => $request->input('article_id', 0),  //文章编号
            'comment_content' => $request->input('comment_content', ''),  //评论内容
            'comment_is_show' => $request->boolean('comment_is_show', false), //是显示(BOOL):0-否;1-是
            'user_id' => User::getUserId()       //发布用户ID
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $formatted_request = $this->formatRequest($request);
        $data = $this->articleCommentService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $comment_id = $request['comment_id'];
        $formatted_request = $this->formatRequest($request);

        $data = $this->articleCommentService->edit($comment_id, $formatted_request);

        return Respond::success($data);

    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $comment_id = $request->get('comment_id');
        $data = $this->articleCommentService->remove($comment_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     */
    public function removeBatch(Request $request)
    {
        $comment_id_str = $request->get('comment_id');
        $comment_ids = explode(',', $comment_id_str);
        $data = $this->articleCommentService->remove($comment_ids);

        return Respond::success($data);
    }


    /**
     * 修改状态值
     */
    public function editState(Request $request)
    {
        $comment_id = $request->input('comment_id', 0);
        $state_data = [
            'comment_is_show' => $request->boolean('comment_is_show')
        ];

        $data = $this->articleCommentService->edit($comment_id, $state_data);

        return Respond::success($data);
    }

}
