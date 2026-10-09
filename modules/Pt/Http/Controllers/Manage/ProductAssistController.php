<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductAssistCriteria;
use Modules\Pt\Repositories\Validators\ProductAssistValidator;
use Modules\Pt\Services\ProductAssistService;

class ProductAssistController extends BaseController
{
    private $productAssistService;
    private $productAssistValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductAssistService $productAssistService, ProductAssistValidator $productAssistValidator)
    {
        $this->productAssistService = $productAssistService;
        $this->productAssistValidator = $productAssistValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productAssistService->list($request, new ProductAssistCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productAssistValidator->with($request->all())->passesOrFail('create');
        $data = $this->productAssistService->add([
            'category_id' => $request->input('category_id', 0), //备注分类
            'assist_is_search' => $request->boolean('assist_is_search', false),
            'assist_name' => $request['assist_name'], //属性名称
            'assist_sort' => $request->input('assist_sort', 0)   //排序
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $assist_id = $request['assist_id'];
        $this->productAssistValidator->setId($assist_id);
        $this->productAssistValidator->with($request->all())->passesOrFail('update');
        $data = $this->productAssistService->edit($assist_id, [
            'category_id' => $request->input('category_id', 0), //备注分类
            'assist_is_search' => $request->boolean('assist_is_search', false),
            'assist_name' => $request['assist_name'], //属性名称
            'assist_sort' => $request->input('assist_sort', 0)   //排序
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $assist_id = $request->get('assist_id', -1);
        $data = $this->productAssistService->remove($assist_id);

        return Respond::success($data);
    }


    /**
     * 属性树形列表
     */
    public function tree(Request $request)
    {
        $data = $this->productAssistService->getTree();

        return Respond::success($data);
    }


}
