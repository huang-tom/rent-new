<?php

namespace Modules\Cms\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Cms\Repositories\Criteria\ArticleTagCriteria;
use Modules\Cms\Repositories\Validators\ArticleTagValidator;
use Modules\Cms\Services\ArticleTagService;

class ArticleTagController extends BaseController
{
    private $articleTagService;
    private $articleTagValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ArticleTagService   $articleTagService,
        ArticleTagValidator $articleTagValidator
    )
    {
        $this->articleTagService = $articleTagService;
        $this->articleTagValidator = $articleTagValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->articleTagService->list($request, new ArticleTagCriteria($request));

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action, $id = null)
    {
        if ($id) {
            $this->articleTagValidator->setId($id);
        }
        $this->articleTagValidator->with($request->all())->passesOrFail($action);
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $data = $this->articleTagService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $tag_id = $request['tag_id'];
        $this->validateRequest($request, 'update', $tag_id);
        $flag = $this->articleTagService->edit($tag_id, $request);

        if ($flag) {
            return Respond::success([$flag]);
        } else {
            return Respond::error('update fail');
        }

    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $tag_id = $request->input('tag_id', -1);
        $data = $this->articleTagService->removeTag($tag_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     */
    public function removeBatch(Request $request)
    {
        $data = $this->articleTagService->removeBatch($request);
        $msg = sprintf("有 %d 标签被成功删除", $data);

        return Respond::success($data, $msg);
    }


}
