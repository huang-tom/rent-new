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

class WechatController extends BaseController
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

        $this->config = $this->configBaseService->getWechatConfig();
    }


    /**
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function getPayParams($request)
    {
        $order_id = $request->get('order_id', -1);
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
            'description' => $this->tradeInfo['trade_title'],
            'amount' => [
                'total' => $this->tradeInfo['trade_amount'] * 100,
            ],
        ];

        return $params;
    }


    public function h5Pay(Request $request)
    {
        $params = $this->getPayParams($request);
        $params['scene_info'] = [
            'payer_client_ip' => $request->ip(),
            'h5_info' => [
                'type' => 'Wap',
            ]
        ];
        $pay = Pay::wechat($this->config)->h5($params);

        $data = [
            'order_id' => $this->orderId,
            'status_code' => 200,
            'statusCode' => 200,
            'paid' => false,
            'code' => 0,
            'mweb_url' => $pay->h5_url,
            'payment_amount' => $this->tradeInfo['trade_amount']
        ];

        return Respond::success($data);
    }


    //PC微信扫码支付
    public function wechatNativePay(Request $request)
    {
        $order = $this->getPayParams($request);
        $result = Pay::wechat($this->config)->scan($order);
        $data = [
            'order_id' => $this->orderId,
            'status_code' => 200,
            'paid' => false,
            'code' => 0,
            'code_url' => $result->code_url,
            'payment_amount' => $this->tradeInfo['trade_amount']
        ];

        return Respond::success($data);
    }


    public function wechatCheck(Request $request)
    {
        $order_id = $request->input('order_id', -1);
        $data = $this->consumeTradeService->checkPaid($order_id);
        if ($data && $data['paid']) {
            return Respond::success($data);
        } else {
            return Respond::error();
        }
    }


    public function wechatNotify(Request $request)
    {
        $pay = Pay::wechat($this->config);

        try {
            $notify_object = $pay->callback(); // 是的，验签就这么简单！
            Log::info($notify_object);

            // 处理业务逻辑，例如更新订单状态
            $notify_data = $notify_object->toArray();
            $notify_resource = $notify_data['resource'];
            $data = $notify_resource['ciphertext'];

            // 具体的业务操作
            if ($data && $data['trade_state'] == 'SUCCESS') {

                // 交易订单号
                $out_trade_no = $data['out_trade_no'];

                // 订单编号
                $order_id = $out_trade_no;
                /*$consumeCombine = $combineRepository->get($outTradeNo);
                if ($consumeCombine !== null) {
                    $orderId = $consumeCombine->getOrderIds();
                } else {
                    $orderId = $outTradeNo;
                }*/

                $payer = $data['payer'] ?? [];
                $amount = $data['amount'] ?? [];

                $consume_deposit = [
                    'deposit_no' => $out_trade_no,
                    'deposit_trade_no' => $data['transaction_id'],
                    'order_id' => $order_id,
                    'deposit_subject' => $data['attach'],
                    'deposit_quantity' => (int)(isset($data['quantity']) ? $data['quantity'] : 0),
                    'deposit_notify_time' => getDateTime(),
                    'deposit_seller_id' => $data['mchid'] ?? '',
                ];

                if (!empty($amount['total'])) {
                    $consume_deposit['deposit_total_fee'] = ($amount['total'] / 100);
                    $consume_deposit['deposit_price'] = ($amount['total'] / 100);
                }

                $consume_deposit['deposit_buyer_id'] = $payer['openid'];
                $consume_deposit['deposit_time'] = getTime();
                $consume_deposit['deposit_payment_type'] = StateCode::PAYMENT_TYPE_ONLINE;
                $consume_deposit['deposit_service'] = isset($data['trade_type']) ? $data['trade_type'] : '';
                $consume_deposit['deposit_sign'] = isset($data['sign']) ? $data['sign'] : '';
                $consume_deposit['deposit_extra_param'] = json_encode($consume_deposit);
                $consume_deposit['payment_channel_id'] = StateCode::PAYMENT_CHANNEL_WECHAT;
                $consume_deposit['deposit_trade_status'] = isset($data['trade_state']) ? $data['trade_state'] : '';

                $store_id = 0;
                $chain_id = 0;

                /*$trade_mode = $this->configBaseService->getConfig('trade_mode');
                if ($trade_mode != 1) {
                    $consumeTrade = $tradeRepository->findOne(['order_id' => $outTradeNo]);
                    if ($consumeTrade !== null) {
                        $storeId = $consumeTrade->getStoreId();
                        $chainId = $consumeTrade->getChainId();
                    }
                }*/

                $consume_deposit['store_id'] = $store_id;
                $consume_deposit['chain_id'] = $chain_id;

                $this->consumeDepositService->processDeposit($consume_deposit);

                return $pay->success(); // 返回成功通知
            } else {
                return response('fail', 500); // 返回失败响应
            }

        } catch (\Exception $e) {
            // 记录错误日志
            Log::error(__('微信支付回调失败: ') . $e->getMessage());

            // 返回失败响应
            return response('fail', 500);
        }
    }


}
