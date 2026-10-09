<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pt\Repositories\Criteria\ProductCategoryCriteria;
use Modules\Pt\Services\ProductCategoryService;
use Modules\Pt\Repositories\Validators\ProductCategoryValidator;

class ProductCategoryController extends BaseController
{
    private $productCategoryService;
    private $productCategoryValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductCategoryService $productCategoryService, ProductCategoryValidator $productCategoryValidator)
    {
        $this->productCategoryService = $productCategoryService;
        $this->productCategoryValidator = $productCategoryValidator;
    }


    /**
     * 列表
     */
    public function tree(Request $request)
    {
        $data = $this->productCategoryService->tree($request);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productCategoryService->list($request, new ProductCategoryCriteria($request));

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
            'category_name' => $request['category_name'],   //分类名称
            'category_parent_id' => $request->input('category_parent_id', 0), //上级分类编号
            'category_image' => $request->input('category_image', ''),   //分类图片
            'type_id' => $request->input('type_id', 0),   //所属类型编号
            'category_commission_rate' => $request->input('category_commission_rate', 0),   //分佣比例
            'category_sort' => $request->input('category_sort', 0), //排序
            'category_is_enable' => $request->boolean('category_is_enable') //是否启用(BOOL):0-不显示;1-显示
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productCategoryValidator->with($request->all())->passesOrFail('create');
        $data = $this->productCategoryService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $this->productCategoryValidator->setId($category_id);
        $this->productCategoryValidator->with($request->all())->passesOrFail('update');
        $data = $this->productCategoryService->edit($category_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $data = $this->productCategoryService->remove($category_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $state_data = [];
        if ($request->has('category_is_enable')) {
            $state_data['category_is_enable'] = $request->boolean('category_is_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $data = $this->productCategoryService->edit($category_id, $state_data);
        } else {
            throw new ErrorException(__('无修改数据'));
        }

        return Respond::success($data);
    }
}
