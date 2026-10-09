<?php

namespace Modules\Sys\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\PhoneNumberUtils;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Sys\Repositories\Models\SmsRecord;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\SmsRecordService;
use Modules\Sys\Services\VerifyCodeService;

class CaptchaController extends BaseController
{
    private $configBaseService;
    private $verifyCodeService;
    private $smsRecordService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ConfigBaseService $configBaseService,
        VerifyCodeService $verifyCodeService,
        SmsRecordService $smsRecordService
    ) {
        $this->configBaseService = $configBaseService;
        $this->verifyCodeService = $verifyCodeService;
        $this->smsRecordService = $smsRecordService;
    }


    /**
     * 图形验证码
     */
    public function index(Request $request)
    {
        $verify_key = $request->get('verify_key');
        $builder = new CaptchaBuilder(4);
        $builder->build(120);

        $code = $builder->getPhrase();
        $expiredAt = Carbon::now()->addMinutes(1);
        Cache::put($verify_key, $code, $expiredAt);

        header('Content-type: image/jpeg');
        $builder->output();
    }


    /**
     * 发送短信验证码
     */
    public function sendMobileVerifyCode(Request $request)
    {

        $verify_key = $request->input('mobile');
        if (!PhoneNumberUtils::isValidNumber($verify_key)) {
            throw new ErrorException('手机号码不准确！');
        }

        $phoneModelWithCountry = PhoneNumberUtils::getPhoneModelWithCountry($verify_key);
        if ($phoneModelWithCountry === null) {
            throw new ErrorException("手机号码解析失败！");
        }

        $mobile = $phoneModelWithCountry->nationalNumber;
        $service_user_id = $this->configBaseService->getConfig('service_user_id', '');
        $service_app_key = $this->configBaseService->getConfig('service_app_key', '');

        $verify_code = getVerifyCode(4);
        $sms_params = [
            'rtime' => time(),
            'app_id_from' => 100,
            'user_id_from' => $service_user_id,
            'service_app_key' => $service_app_key,
            'sms_mobile' => (string)$mobile,
            'sms_content' => sprintf("您的验证码: [%s] 5分钟内有效", $verify_code),
        ];

        // [新增 2026-09-22] 短信场景，供后台「短信记录」筛选。默认按验证码记录。
        $sms_scene = (string)$request->input('scene', 'verify_code');

        try {
            // +------------------------------------------------------------------
            // | [本地化 2026-09-22] 原实现硬编码厂商短信中转地址：
            // |   https://account.shopsuite.cn/index.php?mdu=service&ctl=Sms&met=send&typ=json&t=1
            // | 该地址依赖厂商账号（service_app_key）与厂商短信条数，是一处对外部域名的
            // | 运行时依赖。现改为从 sys_config_base.sms_gateway_url 读取，
            // | 便于接入自建或第三方短信通道；未配置时直接抛错，
            // | 不再静默回落到厂商域名。
            // | 请求体结构与厂商协议保持一致（rtime / app_id_from / user_id_from /
            // | service_app_key / sms_mobile / sms_content），自建网关按此约定接收即可。
            // +------------------------------------------------------------------
            $url = $this->configBaseService->getConfig('sms_gateway_url', '');
            if (empty($url)) {
                throw new ErrorException(__('短信通道未配置，请先在 sys_config_base 中设置 sms_gateway_url'));
            }

            $response = Http::withOptions(['verify' => false])->asForm()->post($url, $sms_params);
            $result = $response->body();
            $result = json_decode($result, true);
            $flag = isset($result['data']) ? $result['data'] : null;
            // +----------------------------------------------------------------------
            // | [本地安全修复 2026-09-22] P0 账户接管 —— 已删除下面这行：
            // |     $flag['verifycode'] = $verify_code;
            // |
            // | 原因（实测复现，非推测）：
            // |   该行把明文短信验证码塞进响应体，而此验证码与下面这些流程
            // |   共用同一份缓存键（Cache::put($mobile, $code)，见 VerifyCodeService）
            // |       - POST /front/account/login/setNewPassword   重置密码（无需登录）
            // |       - GET  /front/account/login/doSmsLogin       短信登录
            // |       - POST /front/account/user/bindMobile        绑定手机号
            // |       - POST /front/account/user/unBindMobile      解绑手机号
            // |   于是任何人只要请求
            // |       GET /front/sys/captcha/mobile?mobile=目标手机号
            // |   就能从响应里直接读到验证码，随后调用 setNewPassword 重置该手机号
            // |   所绑定账号的密码 —— 构成完整的远程账户接管链路。
            // |
            // | 实测证据（修复前，8000 环境）：
            // |   GET /front/sys/captcha/mobile?mobile=13800000000
            // |   → {"status":200,"data":{"msg":"当前账号短信可用条数不足",
            // |       "status":250,"verifycode":"0362"}}   ← 明文验证码
            // +----------------------------------------------------------------------
        } catch (\Exception $e) {
            // [新增 2026-09-22] 失败也留痕：以前短信发不出去是"无声"的，
            // 运维只能从用户投诉里知道。现在后台「短信记录」能直接看到失败原因。
            $this->logSmsRecord($mobile, $sms_params['sms_content'], $sms_scene, SmsRecord::STATUS_FAIL, $e->getMessage());
            throw new ErrorException($e->getMessage());
        }

        // [本地安全修复 2026-09-22] 只有确认厂商侧下发成功才写入验证码缓存。
        // 原实现无论厂商返回什么（含"短信条数不足"这类失败）都会写入缓存并返回成功，
        // 等于「短信没发出去，验证码却已经可用」。此处改为失败即抛错、不写缓存。
        // 注意：若后续接入真实短信账号，需按厂商实际成功返回结构复核 status 取值。
        if (empty($flag) || (isset($flag['status']) && $flag['status'] != 200)) {
            $this->logSmsRecord(
                $mobile,
                $sms_params['sms_content'],
                $sms_scene,
                SmsRecord::STATUS_FAIL,
                isset($flag['msg']) ? $flag['msg'] : '网关未返回可判定结果'
            );
            throw new ErrorException(isset($flag['msg']) ? $flag['msg'] : __('短信发送失败'));
        }

        $this->logSmsRecord(
            $mobile,
            $sms_params['sms_content'],
            $sms_scene,
            SmsRecord::STATUS_SUCCESS,
            isset($flag['msg']) ? (string)$flag['msg'] : 'ok'
        );

        $this->verifyCodeService->setVerifyCode($verify_key, $verify_code);

        return Respond::success($flag);

    }


    /**
     * 写一条短信发送记录
     *
     * ⚠️ 刻意只存「内容」不额外存验证码本身：内容里已经含验证码（与真实下发短信一致），
     *    但记录只在后台（需管理员登录 + manage/sys 权限）可读，不再像修复前那样把
     *    验证码回给任何匿名调用者。若担心日志内泄，可在网关侧改为模板短信。
     *
     * @param string $mobile
     * @param string $content
     * @param string $scene
     * @param int $status
     * @param string $gateway_msg
     * @return void
     */
    private function logSmsRecord($mobile, $content, $scene, $status, $gateway_msg = '')
    {
        $this->smsRecordService->write([
            'user_id'         => $this->smsRecordService->matchUserId($mobile),
            'sms_mobile'      => (string)$mobile,
            'sms_content'     => (string)$content,
            'sms_scene'       => (string)$scene,
            'sms_status'      => $status,
            'sms_gateway_msg' => (string)$gateway_msg,
        ]);
    }


}
