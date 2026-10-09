<?php

namespace Modules\Pay\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\StateCode;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Services\ConsumeDepositService;
use Modules\Pay\Services\ConsumeTradeService;

use Modules\Sys\Services\ConfigBaseService;
use Yansongda\Pay\Pay;

class AlipayController extends BaseController
{
    private $consumeTradeService;
    private $configBaseService;
    private $consumeDepositService;


    protected $config;

    private $orderId = '';
    private $tradeInfo = [];


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ConsumeTradeService   $consumeTradeService,
        ConfigBaseService     $configBaseService,
        ConsumeDepositService $consumeDepositService
    )
    {
        $this->consumeTradeService = $consumeTradeService;
        $this->configBaseService = $configBaseService;
        $this->consumeDepositService = $consumeDepositService;

        $this->config = $this->configBaseService->getAlipayConfig();
    }


    public function getPayParams($request)
    {
        $order_id = $request->get('order_id', 0);
        $this->orderId = $order_id;
        if (!$order_id) {
            throw new ErrorException(__('缺少参数订单号'));
        }

        $this->tradeInfo = $this->consumeTradeService->getTradeInfo($order_id);
        if (empty($this->tradeInfo)) {
            throw new ErrorException(__('交易信息有误'));
        }

        $params = [
            'out_trade_no' => $this->orderId,
            'total_amount' => $this->tradeInfo['trade_amount'],
            'subject' => $this->tradeInfo['trade_title']
        ];

        return $params;
    }

    public function pcPay(Request $request)
    {
        $order = $this->getPayParams($request);
        $order['_return_rocket'] = true;

        $result = Pay::alipay($this->config)->web($order);
        $data = $this->getPayReturn($result);

        return Respond::success($data);
    }


    /**
     * 返回数据
     * @param $result
     * @return array
     */
    private function getPayReturn($result)
    {
        $result = $result->toArray();
        $mweb_url = $result['radar']['url'] . '&' . $result['radar']['body'];

        $data['paid'] = false;
        $data['web_url'] = $mweb_url;
        $data['mwebUrl'] = $mweb_url;
        $data['status_code'] = 200;
        $data['statusCode'] = 200;
        $data['orderId'] = $this->orderId;
        $data['order_id'] = $this->orderId;

        return $data;
    }


    /**
     * 支付宝网页支付
     */
    public function pay(Request $request)
    {
        $return_flag = $request->get('return_flag', 0);
        $order = $this->getPayParams($request);

        if ($return_flag) {
            $order['_return_rocket'] = true;
            $result = Pay::alipay($this->config)->h5($order);
            $data = $this->getPayReturn($result);
        } else {
            $result = Pay::alipay($this->config)->h5($order);

            $data = $result;
            $data['paid'] = false;
            $data['status_code'] = 200;
            $data['statusCode'] = 200;
            $data['order_id'] = $this->orderId;
        }

        return Respond::success($data);
    }


    /**
     * 同步通知
     */
    public function alipayReturn()
    {
        $data = Pay::alipay($this->config)->callback(); // 是的，验签就这么简单！
        $data = $data->toArray();

        $url = env('URL_PC') . "/user/order/detail?init_pay_flag=1&order_id=" . $this->orderId;

        return redirect($url);
    }


    /**
     * 异步通知
     */
    public function alipayNotify()
    {
        $alipay = Pay::alipay($this->config);

        try {
            $notify_object = $alipay->callback(); // 是的，验签就这么简单！
            Log::info($notify_object);

            $data = $notify_object->toArray();
            if ($data['trade_status'] == 'TRADE_SUCCESS' || $data['trade_status'] == 'TRADE_FINISHED') {

                // 交易订单号
                $out_trade_no = $data['out_trade_no'];
                $order_id = $out_trade_no;

                $consume_deposit = [
                    'deposit_no' => $out_trade_no,
                    'deposit_trade_no' => $data['trade_no'],
                    'order_id' => $order_id,
                    'deposit_subject' => $data['subject'],
                    'deposit_quantity' => (int)(isset($data['quantity']) ? $data['quantity'] : 0),
                    'deposit_notify_time' => getDateTime(),
                    'deposit_seller_id' => $data['seller_id'] ?? '',
                ];

                $consume_deposit['deposit_total_fee'] = $data['total_amount'];
                $consume_deposit['deposit_price'] = $data['total_amount'];

                $consume_deposit['deposit_buyer_id'] = $data['buyer_id'];
                $consume_deposit['deposit_time'] = getTime();
                $consume_deposit['deposit_payment_type'] = StateCode::PAYMENT_TYPE_ONLINE;
                $consume_deposit['deposit_service'] = isset($data['trade_type']) ? $data['trade_type'] : '';
                $consume_deposit['deposit_sign'] = '';
                $consume_deposit['deposit_extra_param'] = json_encode($consume_deposit);
                $consume_deposit['payment_channel_id'] = StateCode::PAYMENT_CHANNEL_ALIPAY;
                $consume_deposit['deposit_trade_status'] = $data['trade_status'];

                $store_id = 0;
                $chain_id = 0;
                $consume_deposit['store_id'] = $store_id;
                $consume_deposit['chain_id'] = $chain_id;

                $this->consumeDepositService->processDeposit($consume_deposit);

                return $alipay->success();
            } else {
                return response('fail', 500); // 返回失败响应
            }

        } catch (\Exception $e) {
            // 记录错误日志
            Log::error(__('支付宝支付回调失败: ') . $e->getMessage());

            // 返回失败响应
            return response('fail', 500);
        }
    }


}
