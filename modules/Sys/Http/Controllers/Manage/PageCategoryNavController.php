<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\PageCategoryNavCriteria;
use Modules\Sys\Repositories\Validators\PageCategoryNavValidator;
use Modules\Sys\Services\PageCategoryNavService;

class PageCategoryNavController extends BaseController
{
    private $pageCategoryNavService;
    private $pageCategoryNavValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PageCategoryNavService $pageCategoryNavService, PageCategoryNavValidator $pageCategoryNavValidator)
    {
        $this->pageCategoryNavService = $pageCategoryNavService;
        $this->pageCategoryNavValidator = $pageCategoryNavValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pageCategoryNavService->list($request, new PageCategoryNavCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'category_nav_name' => $request['category_nav_name'],
            'category_nav_image' => $request->input('category_nav_image', ''),
            'category_ids' => $request->input('category_ids', ''),
            'item_ids' => $request->input('item_ids', ''),
            'brand_ids' => $request->input('brand_ids', ''),
            'category_nav_adv' => $request->input('category_nav_adv', '[]'),
            'category_nav_type' => $request->input('category_nav_type', 1),
            'category_nav_order' => $request->input('category_nav_order', 50),
            'category_nav_enable' => $request->boolean('category_nav_enable')
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->pageCategoryNavValidator->with($request->all())->passesOrFail('create');
        $data = $this->pageCategoryNavService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $category_nav_id = $request['category_nav_id'];

        $this->pageCategoryNavValidator->setId($category_nav_id);
        $this->pageCategoryNavValidator->with($request->all())->passesOrFail('update');
        $data = $this->pageCategoryNavService->edit($category_nav_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pageCategoryNavService->remove($request['category_nav_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $category_nav_id = $request['category_nav_id'];
        $state_data = [];
        $state_data['category_nav_enable'] = $request->boolean('category_nav_enable', 0);

        // 更新状态
        if ($category_nav_id && !empty($state_data)) {
            $this->pageCategoryNavService->edit($category_nav_id, $state_data);
        } else {
            throw new ErrorException('数据有误');
        }

        return Respond::success($state_data);
    }


}
