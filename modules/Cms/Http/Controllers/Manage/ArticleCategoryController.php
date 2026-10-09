<?php

namespace Modules\Cms\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Cms\Repositories\Validators\ArticleCategoryValidator;
use Modules\Cms\Services\ArticleCategoryService;

class ArticleCategoryController extends Controller
{
    private $articleCategoryService;
    private $articleCategoryValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleCategoryService $articleCategoryService, ArticleCategoryValidator $articleCategoryValidator)
    {
        $this->articleCategoryService = $articleCategoryService;
        $this->articleCategoryValidator = $articleCategoryValidator;
    }


    /**
     * 分类列表
     */
    public function tree(Request $request)
    {
        $data = $this->articleCategoryService->tree($request);

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
            'category_name' => $request['category_name'],       //分类名称
            'category_parent_id' => $request->input('category_parent_id', 0),  //上级编号
            'category_image_url' => $request->input('category_image_url', ''),  //分类图标
            'category_desc' => $request['category_desc'],       //分类描述
            'category_order' => $request->input('category_order', 0)       //分类排序
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->articleCategoryValidator->with($request->all())->passesOrFail('create');

        $data = $this->articleCategoryService->addCategory($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $category_id = $request->input('category_id', -1);
        $this->articleCategoryValidator->setId($category_id);
        $this->articleCategoryValidator->with($request->all())->passesOrFail('update');

        $data = $this->articleCategoryService->edit($category_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $category_id = $request->input('category_id', -1);
        $data = $this->articleCategoryService->removeCategory($category_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-23] 补 `/manage/cms/articleCategory/removeBatch`（原先 404，
     * 前端 index.vue:222 传逗号串 `{category_id: "1,2,3"}`）。
     */
    public function removeBatch(Request $request)
    {
        $category_id = $request->input('category_id', '');
        $data = $this->articleCategoryService->removeCategoryBatch($category_id);

        return Respond::success($data, __('删除成功'));
    }

}
