<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductTagCriteria;
use Modules\Pt\Repositories\Validators\ProductTagValidator;
use Modules\Pt\Services\ProductTagService;

class ProductTagController extends BaseController
{
    private $productTagService;
    private $productTagValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductTagService $productTagService, ProductTagValidator $productTagValidator)
    {
        $this->productTagService = $productTagService;
        $this->productTagValidator = $productTagValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productTagService->list($request, new ProductTagCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productTagValidator->with($request->all())->passesOrFail('create');
        $data = $this->productTagService->add([
            'product_tag_name' => $request['product_tag_name']   //名称
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $product_tag_id = $request->get('product_tag_id', -1);
        $this->productTagValidator->setId($product_tag_id);
        $this->productTagValidator->with($request->all())->passesOrFail('update');
        $data = $this->productTagService->edit($product_tag_id, [
            'product_tag_name' => $request['product_tag_name']   //名称
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $product_tag_id = $request->get('product_tag_id', -1);
        $data = $this->productTagService->remove($product_tag_id);

        return Respond::success($data);
    }
}
