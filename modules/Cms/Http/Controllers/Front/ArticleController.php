<?php

namespace Modules\Cms\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Cms\Repositories\Criteria\ArticleCategoryCriteria;
use Modules\Cms\Services\ArticleBaseService;
use Modules\Cms\Services\ArticleCategoryService;

class ArticleController extends BaseController
{
    private $articleBaseService;
    private $articleCategoryService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleBaseService $articleBaseService, ArticleCategoryService $articleCategoryService)
    {
        $this->articleBaseService = $articleBaseService;
        $this->articleCategoryService = $articleCategoryService;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->articleBaseService->getLists($request);

        return Respond::success($data);
    }


    public function get(Request $request)
    {
        $article_id = $request->get('article_id');
        $data = $this->articleBaseService->get($article_id);

        return Respond::success($data);
    }


    public function listCategory(Request $request)
    {
        $request['size'] = 100;
        $data = $this->articleCategoryService->list($request, new ArticleCategoryCriteria($request));

        return Respond::success($data);
    }

}
