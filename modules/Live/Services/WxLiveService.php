<?php

namespace Modules\Live\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Services\WechatService;
use Illuminate\Support\Facades\Http;

/**
 * Class WxLiveService.
 *
 * @package Modules\Live\Services
 */
class WxLiveService extends BaseService
{
    private $wechatService;

    public function __construct(WechatService $wechatService)
    {
        $this->wechatService = $wechatService;
    }


    public function getApproved($offset = 1, $limit = 20, $status = 2)
    {

        $url = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token=" . $this->wechatService->getXcxAccessToken(true);
        $params = [
            'offset' => 1,
            'limit' => $limit,
            'status' => $status,
        ];

        $response = Http::get($url, $params);
        $response_str = $response->body();
        $jsonObject = json_decode($response_str);
        $goods = $jsonObject->goods;
        $goodVoList = Collection::make($goods)->mapInto(WxLiveGoodVo::class);

        $approvedRes = new WxApprovedRes();
        $approvedRes->setTotal((int)$jsonObject->total);
        $approvedRes->setGoodVoList($goodVoList->all());

        return $approvedRes;
    }

}
