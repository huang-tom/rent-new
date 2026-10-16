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
    const GET_PHONE_URL = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=ACCESS_TOKEN";
    const CODE2SESSION_URL = "https://api.weixin.qq.com/sns/jscode2session";
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


    /**
     * 用 wx.login 的 code 换取 openid（code 只能使用一次）
     * @param string $code
     * @return array openid / unionid
     * @throws ErrorException
     */
    public function code2Session($code)
    {
        $code = trim((string)$code);
        if ($code === '') {
            throw new ErrorException('登录凭证不能为空');
        }

        $response = Http::get(self::CODE2SESSION_URL, [
            'appid' => $this->configBaseService->getConfig('wechat_xcx_app_id'),
            'secret' => $this->configBaseService->getConfig('wechat_xcx_app_secret'),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ]);
        if (!$response->successful()) {
            throw new ErrorException('微信登录失败');
        }

        $data = $response->json();
        if (!empty($data['errcode'])) {
            throw new ErrorException($data['errmsg'] ?? '微信登录失败');
        }

        $openid = trim((string)($data['openid'] ?? ''));
        if ($openid === '') {
            throw new ErrorException('微信登录失败');
        }

        return [
            'openid' => $openid,
            'unionid' => (string)($data['unionid'] ?? ''),
        ];
    }


    /**
     * 用小程序 getPhoneNumber 回调的 code 换取手机号
     * code 与 wx.login 的 code 不同，5 分钟内且只能使用一次
     * @param string $code
     * @return array phone_number / pure_phone_number / country_code
     * @throws ErrorException
     */
    public function getUserPhoneNumber($code)
    {
        $code = trim((string)$code);
        if ($code === '') {
            throw new ErrorException('手机号凭证不能为空');
        }

        return $this->requestUserPhoneNumber($code, true);
    }


    /**
     * @param string $code
     * @param bool $retry 凭证失效时刷新 access_token 再请求一次
     * @return array
     * @throws ErrorException
     */
    private function requestUserPhoneNumber($code, $retry)
    {
        $access_token = $this->getXcxAccessToken(true);
        $url = str_replace('ACCESS_TOKEN', $access_token, self::GET_PHONE_URL);
        $response = Http::post($url, ['code' => $code]);
        if (!$response->successful()) {
            throw new ErrorException('获取手机号失败');
        }

        $data = $response->json();
        $errcode = (int)($data['errcode'] ?? -1);
        if ($retry && in_array($errcode, [40001, 42001, 40014], true)) {
            $this->getXcxToken();
            return $this->requestUserPhoneNumber($code, false);
        }
        if ($errcode !== 0) {
            throw new ErrorException($data['errmsg'] ?? '获取手机号失败');
        }

        $phone_info = $data['phone_info'] ?? [];
        $pure_phone_number = trim((string)($phone_info['purePhoneNumber'] ?? ''));
        if ($pure_phone_number === '') {
            throw new ErrorException('获取手机号失败');
        }

        return [
            'phone_number' => trim((string)($phone_info['phoneNumber'] ?? $pure_phone_number)),
            'pure_phone_number' => $pure_phone_number,
            'country_code' => trim((string)($phone_info['countryCode'] ?? '86')),
        ];
    }


}
