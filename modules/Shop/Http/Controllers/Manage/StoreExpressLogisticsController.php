<?php

namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreExpressLogisticsCriteria;
use Modules\Shop\Repositories\Validators\StoreExpressLogisticsValidator;
use Modules\Shop\Services\StoreExpressLogisticsService;
use Modules\Trade\Services\OrderLogisticsService;

class StoreExpressLogisticsController extends ShopController
{
    private $storeExpressLogisticsService;
    private $storeExpressLogisticsValidator;
    private $orderLogisticsService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        StoreExpressLogisticsService $storeExpressLogisticsService,
        StoreExpressLogisticsValidator $storeExpressLogisticsValidator,
        OrderLogisticsService $orderLogisticsService
    )
    {
        $this->storeExpressLogisticsService = $storeExpressLogisticsService;
        $this->storeExpressLogisticsValidator = $storeExpressLogisticsValidator;
        $this->orderLogisticsService = $orderLogisticsService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeExpressLogisticsService->list($request, new StoreExpressLogisticsCriteria($request));

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
            'logistics_name' => $request['logistics_name'],   //物流名称
            'express_id' => $request['express_id'],       //快递编号
            'express_name' => $request['express_name'],     //快递名称
            'logistics_number' => $request->input('logistics_number', 0),   //公司编号
            'logistics_fee' => $request->input('logistics_fee', 0),   //物流运费
            'logistics_intl' => $request->input('logistics_intl', '+86'), //国家编号
            'logistics_mobile' => $request->input('logistics_mobile', ''),    //手机号码
            'logistics_contacter' => $request->input('logistics_contacter', ''), //联系人
            'logistics_address' => $request->input('logistics_address', ''),   //联系地址
            'logistics_is_enable' => $request->boolean('logistics_is_enable'),   //是否启用(BOOL):1-启用;0-禁用
            'logistics_is_default' => $request->boolean('logistics_is_default'),  //是否为默认(BOOL):1-默认;0-非默认
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
        $data = $this->storeExpressLogisticsService->addExpressLogistics($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $logistics_id = $request['logistics_id'];
        $this->validateRequest($request, 'update');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeExpressLogisticsService->editExpressLogistics($logistics_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $logistics_id = $request->input('logistics_id', 0);
        $data = $this->storeExpressLogisticsService->remove($logistics_id);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeExpressLogisticsValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $data = $this->storeExpressLogisticsService->editState($request);

        return Respond::success($data);
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeExpressLogistics/removeBatch`（原先 404）。
     */
    public function removeBatch(Request $request)
    {
        $logistics_id = $request->input('logistics_id', '');
        $data = $this->storeExpressLogisticsService->removeBatch($logistics_id);

        return Respond::success($data, __('删除成功'));
    }


    /**
     * 退货物流跟踪
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeExpressLogistics/returnLogistics`（原先 404）。
     * 调用方是「售后订单 → 物流详情」弹窗（OrderReturnTracking.vue）。
     *
     * ⚠️ 注意这个方法名容易误解：它跟本控制器管的「发货信息（shop_store_express_logistics）」
     *    没有关系，实际查询的是**退货寄回**的快递轨迹（trade_order_return 里的运单号），
     *    之所以挂在 Shop 模块的路由下，纯粹是因为前端把 api 函数写在
     *    `@/api/shop/storeExpressLogistics.ts` 里了。
     *    真正的实现放在 Trade\OrderLogisticsService::returnTrace()，与前台订单轨迹共用快递鸟调用。
     *
     * 这里注入 OrderLogisticsService 而不写进 StoreExpressLogisticsService，
     * 是为了避免 Shop ↔ Trade 的构造函数循环依赖（Trade 已依赖 Shop 的多个仓储）。
     */
    public function returnLogistics(Request $request)
    {
        $data = $this->orderLogisticsService->returnTrace($request);

        return Respond::success($data);
    }

}
