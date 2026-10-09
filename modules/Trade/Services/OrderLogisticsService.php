<?php

namespace Modules\Trade\Services;

use GuzzleHttp\Client;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\ExpressBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository;
use Modules\Trade\Repositories\Contracts\OrderLogisticsRepository;
use App\Exceptions\ErrorException;

/**
 * Class OrderLogisticsService.
 *
 * @package Modules\Trade\Services
 */
class OrderLogisticsService extends BaseService
{

    private $orderDeliveryAddressRepository;
    private $configBaseRepository;
    private $expressBaseRepository;
    private $orderService;

    public function __construct(
        OrderLogisticsRepository       $orderLogisticsRepository,
        OrderDeliveryAddressRepository $orderDeliveryAddressRepository,
        ConfigBaseRepository           $configBaseRepository,
        ExpressBaseRepository          $expressBaseRepository,
        OrderService                   $orderService)
    {
        $this->repository = $orderLogisticsRepository;
        $this->orderDeliveryAddressRepository = $orderDeliveryAddressRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->expressBaseRepository = $expressBaseRepository;
        $this->orderService = $orderService;
    }

    private static $stateMap = [
        "0" => "没有记录",
        "1" => "已揽收",
        "2" => "运输途中",
        "201" => "到达目的城市",
        "202" => "派件中",
        "211" => "已投放快递柜或驿站",
        "3" => "已签收",
        "301" => "正常签收",
        "302" => "派件异常后最终签收",
        "304" => "代收签收",
        "311" => "快递柜或驿站签收",
        "4" => "问题件",
        "401" => "发货无信息",
        "402" => "超时未签收",
        "403" => "超时未更新",
        "404" => "拒收(退件)",
        "405" => "派件异常",
        "406" => "退货签收",
        "407" => "退货未签收",
        "412" => "快递柜或驿站超时未取"
    ];


    /**
     * 新增订单物流
     *
     * [新增 2026-09-22] POST /manage/trade/orderLogistics/add 这条路由在
     * Trade/Routes/web.php:29 一直有声明，但 OrderLogisticsController 只有 edit() 没有 add()。
     * 命中它会抛 NotFoundHttpException → 对外是 404「接口不存在」，与「路由没注册」同表现，
     * 所以路由存在性扫描扫不出来（属 route_method_audit.py 扫出的 11 条静默 404 之一）。
     *
     * 这里显式建行；字段与 edit() 完全对齐，避免两处语义漂移。
     *
     * @param array $row
     * @return array
     * @throws ErrorException
     */
    public function addLogistics(array $row)
    {
        $logistics = OrderLogistics::create($row);
        if (!$logistics) {
            throw new ErrorException(__('操作失败'));
        }

        return $logistics->toArray();
    }


    /**
     * 物流跟踪
     * @param $req
     * @return array
     * @throws ErrorException
     */
    public function trace($req)
    {
        $result = [];
        $order_id = $req->input('order_id', '');
        $order_delivery_address = $this->orderDeliveryAddressRepository->getOne($order_id);
        if (empty($order_delivery_address)) {
            throw new ErrorException(__("未找到收货地址"));
        }

        $mobile = $order_delivery_address['da_mobile'];
        $order_tracking_number = $req->input('order_tracking_number', '');
        if (trim($order_tracking_number) == '') {
            throw new ErrorException(__("订单物流单号为空"));
        }

        $order_logistics_id = $req->input('order_logistics_id', 0);
        $express_id = $req->input('express_id', 0);
        if ($order_logistics_id && !$express_id) {
            $order_logistics_row = $this->repository->getOne($order_logistics_id);
            $express_id = $order_logistics_row['express_id'];
        }

        $channel = $this->configBaseRepository->getConfig('logistics_channel', "kuaidi100");
        $express_base = $this->expressBaseRepository->getOne($express_id);
        if (empty($express_base)) {
            throw new ErrorException(__("快递公司有误！"));
        }

        if ($channel === 'kuaidi100') {
            $shipping_code = $express_base['express_pinyin_100'];
            $logistics_info = $this->kd100($order_tracking_number, $shipping_code, $mobile);
        } else {
            $shipping_code = $express_base['express_pinyin'];
            $logistics_info = $this->kdNiao($order_tracking_number, $shipping_code, $mobile);
        }

        $result['state'] = $logistics_info['State'];
        $result['express_state'] = $logistics_info['express_state'];
        $result['traces'] = $logistics_info['Traces'];

        return $result;
    }


    /**
     * 退货物流跟踪（管理端「售后订单 → 物流详情」）
     *
     * [新增 2026-09-23] 补 `/manage/shop/storeExpressLogistics/returnLogistics`（原先 404）。
     * 调用方：views/trade/orderReturn/components/OrderReturnTracking.vue:75，
     * 传的是 `{return_tracking_name, return_tracking_number}`（注意：**没有 order_id**），
     * 而返回体要的是 `data.traces`（快递鸟的 Traces，字段是 AcceptStation / AcceptTime）。
     *
     * 与前台 trace() 的差别（所以不能直接复用同一个方法）：
     *   1) trace() 第一件事就是拿 order_id 去查收货地址，取 da_mobile ——
     *      退货场景前端不传 order_id，硬套会直接抛"未找到收货地址"。
     *   2) trace() 要 express_id / order_logistics_id，退货只有快递公司**名字**，
     *      所以这里按名字反查 sys_express_base。
     *   3) 退货寄回的快递不一定需要手机号后四位，CustomerName 允许留空。
     *
     * @param $req
     * @return array
     * @throws ErrorException
     */
    public function returnTrace($req)
    {
        $tracking_name = trim((string)$req->input('return_tracking_name', ''));
        $tracking_number = trim((string)$req->input('return_tracking_number', ''));

        if ($tracking_number === '') {
            throw new ErrorException(__('退货物流单号为空'));
        }

        // 按快递公司名反查标准化编码（快递鸟要用拼音码，如 shunfeng / yuantong）
        $express_base = null;
        if ($tracking_name !== '') {
            $express_base = $this->expressBaseRepository->findOne(['express_name' => $tracking_name]);
            if (empty($express_base)) {
                // 名称对不上时退回模糊匹配，避免前端存的名字和基础资料有细微差异就整条查询失败
                $express_base = $this->expressBaseRepository->findOne([['express_name', 'like', '%' . $tracking_name . '%']]);
            }
        }
        if (empty($express_base)) {
            throw new ErrorException(sprintf(__('未匹配到快递公司【%s】，请先在「快递公司」里维护'), $tracking_name ?: '空'));
        }

        $shipping_code = $express_base['express_pinyin'];
        $logistics_info = $this->kdNiao($tracking_number, $shipping_code, '');

        return [
            'state' => $logistics_info['State'],
            'express_state' => $logistics_info['express_state'],
            'traces' => $logistics_info['Traces'],
        ];
    }


    /**
     * 快递鸟接口物流数据
     * @param $order_tracking_number
     * @param $shipping_code
     * @param $mobile
     * @return mixed
     * @throws \Exception
     */
    public function kdNiao($order_tracking_number, $shipping_code, $mobile)
    {
        $logistics_info_str = $this->apiKdNiao($order_tracking_number, $shipping_code, $mobile);
        $logistics_info = json_decode($logistics_info_str, true);

        $state = $logistics_info['State'];
        if ($state == 0) {
            $reason = $logistics_info['Reason'];
            throw new ErrorException(__("非系统错误，请联系管理员检查物流配置项，或检查发货信息是否真实有效！错误信息：{" . $reason . "}"));
        }

        if (!isset($logistics_info['StateEx'])) {
            throw new ErrorException(__("物流状态异常！"));
        }

        $logistics_info['express_state'] = self::$stateMap[$logistics_info['StateEx']];

        return $logistics_info;
    }


    /**
     * 请求快递鸟接口
     * @param $orderTrackingNumber
     * @param $shipperCode
     * @param $CustomerName
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiKdNiao($orderTrackingNumber, $shipperCode, $CustomerName)
    {
        // 组装应用级参数
        $request_data = json_encode([
            'OrderCode' => '',
            'shipperCode' => $shipperCode,
            'CustomerName' => $CustomerName,
            'logisticCode' => $orderTrackingNumber
        ]);

        $app_key = $this->configBaseRepository->getConfig("kuaidiniao_app_key");
        $business_id = $this->configBaseRepository->getConfig("kuaidiniao_e_business_id");
        $api_url = "https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";

        // 组装系统级参数
        $params = [
            'RequestData' => urlencode($request_data),
            'EBusinessID' => $business_id,
            'RequestType' => "8002", // 快递查询接口指令8002/地图版快递查询接口指令8004
            'DataSign' => urlencode($this->encrypt($request_data, $app_key)),
            'DataType' => "2"
        ];

        try {
            $client = new Client(['verify' => false]); // 禁用 SSL 验证
            $response = $client->post($api_url, [
                'form_params' => $params
            ]);
            $result = $response->getBody()->getContents();

            return $result;
        } catch (RequestException $e) {
            throw new \Exception('Error sending POST request: ' . $e->getMessage());
        }
    }


    /**
     * 数据加密方法
     */
    private function encrypt($data, $appKey)
    {
        return base64_encode(md5($data . $appKey));
    }


    //快递100接口
    public function kd100()
    {

    }

}
