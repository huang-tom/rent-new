<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Services\NewOrderPayService;

class NewOrderPayController extends BaseController
{
    private $newOrderPayService;
    private $userId;

    public function __construct(NewOrderPayService $newOrderPayService)
    {
        $this->newOrderPayService = $newOrderPayService;
        $this->userId = User::getUserId();
    }

    /**
     * 微信小程序下单：order_number 为订单号，PO 购买，RO 租赁
     */
    public function wechatMiniPay(Request $request)
    {
        $data = $this->newOrderPayService->wechatMiniPay(
            $request->input('order_number', ''),
            $this->userId
        );

        return Respond::success($data);
    }
}
