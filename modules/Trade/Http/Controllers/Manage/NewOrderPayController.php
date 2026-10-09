<?php

namespace Modules\Trade\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Trade\Services\NewOrderPayService;

class NewOrderPayController extends BaseController
{
    private $newOrderPayService;

    public function __construct(NewOrderPayService $newOrderPayService)
    {
        $this->newOrderPayService = $newOrderPayService;
    }

    /**
     * 支付回调，免登录。order_id 为订单号：PO 购买，RO 租赁
     */
    public function payCallback(Request $request)
    {
        $order_number = $request->input('order_id', '');
        if ($order_number === '' || $order_number === null) {
            $order_number = $request->input('order_number', '');
        }
        $data = $this->newOrderPayService->setPaidYes($order_number, [
            'paid_amount' => $request->input('paid_amount', null),
            'pay_method' => $request->input('pay_method', ''),
            'pay_time' => $request->input('pay_time', null),
            'trade_no' => $request->input('trade_no', ''),
        ]);

        return Respond::success($data);
    }
}
