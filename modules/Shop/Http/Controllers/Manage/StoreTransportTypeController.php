<?php

namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreTransportTypeCriteria;
use Modules\Shop\Services\StoreTransportTypeService;
use Modules\Shop\Repositories\Validators\StoreTransportTypeValidator;

class StoreTransportTypeController extends ShopController
{

    private $storeTransportTypeService;
    private $storeTransportTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreTransportTypeService $storeTransportTypeService, StoreTransportTypeValidator $storeTransportTypeValidator)
    {
        $this->storeTransportTypeService = $storeTransportTypeService;
        $this->storeTransportTypeValidator = $storeTransportTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeTransportTypeService->list($request, new StoreTransportTypeCriteria($request));

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
            'transport_type_name' => $request['transport_type_name'],   //模板名称
            'transport_type_pricing_method' => $request->input('transport_type_pricing_method', 1), //计费规则(ENUM):1-按件数;2-按重量;3-按体积
            'transport_type_freight_free' => $request->input('transport_type_freight_free', 0),   //免运费额度
            'transport_type_free' => $request->boolean('transport_type_free'), //全免运费(BOOL):0-不全免;1-全免（不限制地区且免运费）
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
        $data = $this->storeTransportTypeService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $transport_type_id = $request['transport_type_id'];
        $this->validateRequest($request, 'update');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeTransportTypeService->edit($transport_type_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $transport_type_id = $request->input('transport_type_id', 0);
        $data = $this->storeTransportTypeService->removeType($transport_type_id);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeTransportTypeValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeTransportType/removeBatch`（原先 404）。
     */
    public function removeBatch(Request $request)
    {
        $transport_type_id = $request->input('transport_type_id', '');
        $data = $this->storeTransportTypeService->removeBatchType($transport_type_id);

        return Respond::success($data, __('删除成功'));
    }
}
