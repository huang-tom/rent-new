<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductAssistItemCriteria;
use Modules\Pt\Repositories\Validators\ProductAssistItemValidator;
use Modules\Pt\Services\ProductAssistItemService;

class ProductAssistItemController extends BaseController
{
    private $productAssistItemService;
    private $productAssistItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductAssistItemService $productAssistItemService, ProductAssistItemValidator $productAssistItemValidator)
    {
        $this->productAssistItemService = $productAssistItemService;
        $this->productAssistItemValidator = $productAssistItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productAssistItemService->list($request, new ProductAssistItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productAssistItemValidator->with($request->all())->passesOrFail('create');
        $data = $this->productAssistItemService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $assist_item_id = $request['assist_item_id'];
        $this->productAssistItemValidator->setId($assist_item_id);
        $this->productAssistItemValidator->with($request->all())->passesOrFail('update');
        $data = $this->productAssistItemService->edit($assist_item_id, [
            'assist_id' => $request['assist_id'],     //属性ID
            'assist_item_name' => $request['assist_item_name'], //选项名称
            'assist_item_sort' => $request->input('assist_item_sort', 0)   //排序
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $assist_item_id = $request->get('assist_item_id', -1);
        $data = $this->productAssistItemService->remove($assist_item_id);

        return Respond::success($data);
    }
}
