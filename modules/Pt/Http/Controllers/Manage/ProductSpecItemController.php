<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pt\Repositories\Criteria\ProductSpecItemCriteria;
use Modules\Pt\Services\ProductSpecItemService;
use Modules\Pt\Repositories\Validators\ProductSpecItemValidator;

class ProductSpecItemController extends BaseController
{
    private $productSpecItemService;
    private $productSpecItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductSpecItemService $productSpecItemService, ProductSpecItemValidator $productSpecItemValidator)
    {
        $this->productSpecItemService = $productSpecItemService;
        $this->productSpecItemValidator = $productSpecItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productSpecItemService->list($request, new ProductSpecItemCriteria($request));

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
            'spec_id' => $request['spec_id'],   //规格编号
            'spec_item_name' => $request['spec_item_name'],   //规格名称
            'spec_item_sort' => $request->input('spec_item_sort', 0),   //排序
            'spec_item_enable' => $request->boolean('spec_item_enable')   //是否启用(BOOL):0-不显示;1-显示
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productSpecItemValidator->with($request->all())->passesOrFail('create');
        $data = $this->productSpecItemService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $spec_item_id = $request->get('spec_item_id', -1);
        $this->productSpecItemValidator->setId($spec_item_id);
        $this->productSpecItemValidator->with($request->all())->passesOrFail('update');
        $data = $this->productSpecItemService->edit($spec_item_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $spec_item_id = $request->get('spec_item_id', -1);
        $data = $this->productSpecItemService->remove($spec_item_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $spec_item_id = $request->get('spec_item_id');
        $state_data = [];
        if ($request->has('spec_item_enable')) {
            $state_data['spec_item_enable'] = $request->boolean('spec_item_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $this->productSpecItemService->edit($spec_item_id, $state_data);
            return Respond::success($state_data);
        } else {
            throw new ErrorException(__('无修改数据'));
        }
    }

}
