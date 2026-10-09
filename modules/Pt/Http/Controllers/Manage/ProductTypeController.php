<?php

namespace Modules\Pt\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pt\Repositories\Criteria\ProductTypeCriteria;
use Modules\Pt\Services\ProductTypeService;
use Modules\Pt\Repositories\Validators\ProductTypeValidator;

class ProductTypeController extends BaseController
{
    private $productTypeService;
    private $productTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductTypeService $productTypeService, ProductTypeValidator $productTypeValidator)
    {
        $this->productTypeService = $productTypeService;
        $this->productTypeValidator = $productTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productTypeService->list($request, new ProductTypeCriteria($request));

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
            'type_name' => $request['type_name'],   //名称
            'category_id' => $request['category_id'],   //分类ID
            'spec_ids' => $request->input('spec_ids', ''),   //规格ID
            'brand_ids' => $request->input('brand_ids', ''),  //品牌ID
            'assist_ids' => $request->input('assist_ids', '') //属性ID
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productTypeValidator->with($request->all())->passesOrFail('create');
        $data = $this->productTypeService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $type_id = $request['type_id'];
        $this->productTypeValidator->setId($type_id);
        $this->productTypeValidator->with($request->all())->passesOrFail('update');
        $data = $this->productTypeService->edit($type_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $type_id = $request['type_id'];
        $data = $this->productTypeService->remove($type_id);

        return Respond::success($data);
    }


    public function info(Request $request)
    {
        $type_id = $request->get('type_id');
        $data = $this->productTypeService->getInfo($type_id);

        return Respond::success($data);
    }

}
