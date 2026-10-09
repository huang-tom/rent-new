<?php

namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreShippingAddressCriteria;
use Modules\Shop\Services\StoreShippingAddressService;
use Modules\Shop\Repositories\Validators\StoreShippingAddressValidator;

class StoreShippingAddressController extends ShopController
{
    private $storeShippingAddressService;
    private $storeShippingAddressValidator;

    /**
     * Create a new controller instance.
     *
     * @param StoreShippingAddressService $storeShippingAddressService
     * @param StoreShippingAddressValidator $storeShippingAddressValidator
     */
    public function __construct(
        StoreShippingAddressService   $storeShippingAddressService,
        StoreShippingAddressValidator $storeShippingAddressValidator,
    )
    {
        $this->storeShippingAddressService = $storeShippingAddressService;
        $this->storeShippingAddressValidator = $storeShippingAddressValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeShippingAddressService->list($request, new StoreShippingAddressCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        return [
            'ss_name' => $request->input('ss_name', ''),   // 联系人
            'ss_intl' => $request->input('ss_intl', '+86'), // 国家编码
            'ss_mobile' => $request->input('ss_mobile', ''),   // 手机号码
            'ss_postalcode' => $request->input('ss_postalcode', ''), // 邮编
            'ss_province' => $request->input('ss_province', ''), // 省份
            'ss_city' => $request->input('ss_city', ''), // 市
            'ss_county' => $request->input('ss_county', ''), // 县区
            'ss_address' => $request->input('ss_address', ''), // 详细地址
            'ss_province_id' => $request->input('ss_province_id', 0), // 省编号
            'ss_city_id' => $request->input('ss_city_id', 0), // 市编号
            'ss_county_id' => $request->input('ss_county_id', 0), // 县区编号
            'ss_is_default' => $request->boolean('ss_is_default', false), // 默认地址(ENUM):0-否;1-是
        ];
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeShippingAddressService->addShippingAddress($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $ss_id = $request['ss_id'];
        $this->validateRequest($request, 'update');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeShippingAddressService->editShippingAddress($ss_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $ss_id = $request->input('ss_id', 0);
        $data = $this->storeShippingAddressService->remove($ss_id);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeShippingAddressValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeShippingAddress/removeBatch`（原先 404）。
     */
    public function removeBatch(Request $request)
    {
        $ss_id = $request->input('ss_id', '');
        $data = $this->storeShippingAddressService->removeBatch($ss_id);

        return Respond::success($data, __('删除成功'));
    }

}
