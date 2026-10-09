<?php

namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use App\Support\StateCode;
use Illuminate\Support\Facades\Http;
use Modules\Sys\Services\ConfigBaseService;
use Illuminate\Support\Facades\Redis;

/**
 * Class WechatService.
 *
 * @package Modules\Account\Services
 */
class WechatService
{

    const GET_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET";
    private $configBaseService;

    public function __construct(ConfigBaseService $configBaseService)
    {
        $this->configBaseService = $configBaseService;
    }


    /**
     * 获取AccessToken  向外提供
     */
    public function getXcxAccessToken($useCacheFlag)
    {
        if ($useCacheFlag) {
            if (!Redis::exists(StateCode::WX_XCX_ACCESSTOKEN)) {
                return $this->getXcxToken();
            }
            return Redis::get(StateCode::WX_XCX_ACCESSTOKEN);
        } else {
            return $this->getXcxToken();
        }
    }


    /**
     * 发送get请求获取AccessToken
     */
    public function getXcxToken()
    {
        $wechat_app_id = $this->configBaseService->getConfig('wechat_xcx_app_id');
        $wechat_app_secret = $this->configBaseService->getConfig('wechat_xcx_app_secret');
        $url = str_replace(['APPID', 'APPSECRET'], [$wechat_app_id, $wechat_app_secret], WechatService::GET_TOKEN_URL);
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            $access_token = $data['access_token'];
        } else {
            throw new ErrorException('获取小程序access_token失败！');
        }

        $expires_in = $data['expires_in'];
        Redis::set(StateCode::WX_XCX_ACCESSTOKEN,$access_token,$expires_in);

        return $access_token;
    }


}
