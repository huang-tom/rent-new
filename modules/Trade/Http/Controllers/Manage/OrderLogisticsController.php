<?php

namespace Modules\Trade\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Services\StoreExpressLogisticsService;
use Modules\Trade\Services\OrderLogisticsService;
use Modules\Trade\Repositories\Validators\OrderLogisticsValidator;

class OrderLogisticsController extends BaseController
{
    private $orderLogisticsService;
    private $orderLogisticsValidator;
    private $storeExpressLogisticsService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderLogisticsService        $orderLogisticsService,
        OrderLogisticsValidator      $orderLogisticsValidator,
        StoreExpressLogisticsService $storeExpressLogisticsService
    )
    {
        $this->orderLogisticsService = $orderLogisticsService;
        $this->orderLogisticsValidator = $orderLogisticsValidator;
        $this->storeExpressLogisticsService = $storeExpressLogisticsService;
    }


    /**
     * 新增
     *
     * [新增 2026-09-22] 补上一直声明、却从未实现的控制器方法（原为静默 404）。
     * 字段映射与下面的 edit() 保持一致，避免两处语义漂移。
     */
    public function add(Request $request)
    {
        $store_logistics_id = $request['logistics_id'];
        if (!$store_logistics_id) {
            throw new ErrorException(__('请选择快递公司'));
        }

        $store_logistics_row = $this->storeExpressLogisticsService->get($store_logistics_id);
        if (empty($store_logistics_row)) {
            throw new ErrorException(__('快递公司不存在，请先在「快递公司」里维护'));
        }

        $data = $this->orderLogisticsService->addLogistics([
            'order_id' => $request['order_id'],   //订单编号
            'stock_bill_id' => $request['stock_bill_id'],   //出库单号
            'order_tracking_number' => $request['order_tracking_number'], //订单物流单号
            'logistics_id' => $store_logistics_id,   //对应快递公司
            'ss_id' => $request['ss_id'],     //发货地址编号
            'logistics_explain' => $request->input('logistics_explain', ''), //发货备注
            'logistics_time' => $request->input('logistics_time'),
            'express_name' => $store_logistics_row['express_name'],
            'express_id' => $store_logistics_row['express_id'],
            'logistics_phone' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_mobile' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_contacter' => $store_logistics_row['logistics_contacter'],
            'logistics_address' => $store_logistics_row['logistics_address']
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $order_logistics_id = $request['order_logistics_id'];
        $this->orderLogisticsValidator->setId($order_logistics_id);
        $this->orderLogisticsValidator->with($request->all())->passesOrFail('create');
        $store_logistics_id = $request['logistics_id'];
        $store_logistics_row = $this->storeExpressLogisticsService->get($store_logistics_id);
        $data = $this->orderLogisticsService->edit($order_logistics_id, [
            'order_id' => $request['order_id'],   //订单编号
            'stock_bill_id' => $request['stock_bill_id'],   //出库单号
            'order_tracking_number' => $request['order_tracking_number'], //订单物流单号
            'logistics_id' => $store_logistics_id,   //对应快递公司
            'ss_id' => $request['ss_id'],     //发货地址编号
            'logistics_explain' => $request->input('logistics_explain', ''), //发货备注
            'logistics_time' => $request->input('logistics_time'),
            'express_name' => $store_logistics_row['express_name'],
            'express_id' => $store_logistics_row['express_id'],
            'logistics_phone' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_mobile' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_contacter' => $store_logistics_row['logistics_contacter'],
            'logistics_address' => $store_logistics_row['logistics_address']
        ]);

        return Respond::success($data);
    }

}
