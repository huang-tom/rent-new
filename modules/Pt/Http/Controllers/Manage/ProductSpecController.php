<?php

namespace Modules\Pt\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pt\Repositories\Criteria\ProductSpecCriteria;
use Modules\Pt\Services\ProductSpecService;
use Modules\Pt\Repositories\Validators\ProductSpecValidator;

class ProductSpecController extends BaseController
{
    private $productSpecService;
    private $productSpecValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductSpecService $productSpecService, ProductSpecValidator $productSpecValidator)
    {
        $this->productSpecService = $productSpecService;
        $this->productSpecValidator = $productSpecValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productSpecService->list($request, new ProductSpecCriteria($request));

        return Respond::success($data);
    }

    /**
     * 分类结构规格
     */
    public function tree(Request $request)
    {
        $data = $this->productSpecService->tree($request);

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
            'spec_name' => $request['spec_name'],   //规格名称
            'spec_format' => $request->input('spec_format', 'text'), //展现形式
            'spec_sort' => $request->input('spec_sort', 0),   //排序
            'category_id' => $request->input('category_id', 0)   //所属分类
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productSpecValidator->with($request->all())->passesOrFail('create');
        $data = $this->productSpecService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $spec_id = $request->get('spec_id', -1);
        $this->productSpecValidator->setId($spec_id);
        $this->productSpecValidator->with($request->all())->passesOrFail('update');
        $data = $this->productSpecService->edit($spec_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $spec_id = $request->get('spec_id', -1);
        $data = $this->productSpecService->remove($spec_id);

        return Respond::success($data);
    }

}
