<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductBrandCriteria;
use Modules\Pt\Repositories\Validators\ProductBrandValidator;
use Modules\Pt\Services\ProductBrandService;

class ProductBrandController extends BaseController
{
    private $productBrandService;
    private $productBrandValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductBrandService $productBrandService, ProductBrandValidator $productBrandValidator)
    {
        $this->productBrandService = $productBrandService;
        $this->productBrandValidator = $productBrandValidator;
    }


    /**
     * 分类结构品牌
     */
    public function tree(Request $request)
    {
        $data = $this->productBrandService->tree($request);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productBrandService->list($request, new ProductBrandCriteria($request));

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
            'brand_name' => $request['brand_name'],   //品牌名称
            'brand_show_type' => $request->input('brand_show_type', 1),   //展现形式
            'category_id' => $request->input('category_id', 0), //所属分类
            'brand_desc' => $request->input('brand_desc', ''), //品牌描述
            'brand_image' => $request->input('brand_image', ''), //品牌LOGO
            'brand_recommend' => $request->boolean('brand_recommend'),       //是否推荐
            'brand_enable' => $request->boolean('brand_enable')     //是否启用
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productBrandValidator->with($request->all())->passesOrFail('create');
        $data = $this->productBrandService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $brand_id = $request->get('brand_id', -1);
        $this->productBrandValidator->setId($brand_id);
        $this->productBrandValidator->with($request->all())->passesOrFail('update');
        $data = $this->productBrandService->edit($brand_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $brand_id = $request->get('brand_id', -1);
        $data = $this->productBrandService->remove($brand_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $brand_id = $request->get('brand_id', -1);
        $state_data = [];
        if ($request->has('brand_recommend')) {
            $state_data['brand_recommend'] = $request->boolean('brand_recommend');
        }
        if ($request->has('brand_enable')) {
            $state_data['brand_enable'] = $request->boolean('brand_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $data = $this->productBrandService->edit($brand_id, $state_data);
        } else {
            throw new ErrorException(__('无修改数据'));
        }

        return Respond::success($data);
    }

}
