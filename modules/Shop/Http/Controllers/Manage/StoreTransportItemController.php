<?php

namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreTransportItemCriteria;
use Modules\Shop\Services\StoreTransportItemService;
use Modules\Shop\Repositories\Validators\StoreTransportItemValidator;

class StoreTransportItemController extends ShopController
{
    private $storeTransportItemService;
    private $storeTransportItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreTransportItemService $storeTransportItemService, StoreTransportItemValidator $storeTransportItemValidator)
    {
        $this->storeTransportItemService = $storeTransportItemService;
        $this->storeTransportItemValidator = $storeTransportItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeTransportItemService->list($request, new StoreTransportItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeTransportItemValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'transport_type_id' => $request['transport_type_id'],   //模板编号
            'transport_item_default_num' => $request->input('transport_item_default_num', 1),   //默认数量
            'transport_item_default_price' => $request->input('transport_item_default_price', 0), //默认运费
            'transport_item_add_num' => $request->input('transport_item_add_num', 1),       //增加数量
            'transport_item_add_price' => $request->input('transport_item_add_price', 0),     //增加运费
            'transport_item_city_ids' => $request->input('transport_item_city_ids', ''), //区域城市id(DOT)
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeTransportItemService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $transport_item_id = $request['transport_item_id'];
        $this->validateRequest($request, 'update');

        if (!$request->has('transport_type_id') && $request->has('transport_item_city_ids')) {
            $data = $this->storeTransportItemService->edit($transport_item_id, [
                'transport_item_city_ids' => $request['transport_item_city_ids']
            ]);
        } else {
            $formatted_request = $this->formatRequest($request);
            $data = $this->storeTransportItemService->edit($transport_item_id, $formatted_request);
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $transport_item_id = $request['transport_item_id'];
        $data = $this->storeTransportItemService->remove($transport_item_id);

        return Respond::success($data);
    }
}
