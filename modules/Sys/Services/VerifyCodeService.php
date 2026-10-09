<?php

namespace Modules\Sys\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Class VerifyCodeService.
 *
 * @package Modules\Sys\Services
 */
class VerifyCodeService
{

    /**
     * 存储验证码
     * @param $key
     * @param $code
     * @return void
     */
    public function setVerifyCode($key, $code)
    {
        // 将验证码保存到缓存中，设置过期时间为5分钟
        Cache::put($key, $code, 300); // 300秒即5分钟
    }


    /**
     * 验证码验证
     * @param $verify_key
     * @param $verify_code
     * @return bool
     */
    public function checkVerifyCode($verify_key, $verify_code)
    {
        $code = Cache::get($verify_key);
        if (!$code || !$verify_code) {
            return false;
        }

        // +----------------------------------------------------------------------
        // | [本地安全加固 2026-09-22] 验证码失败次数限制
        // |
        // | 原实现（strtolower($verify_code) != strtolower($code) 直接 return false）
        // | 有两个问题：
        // |   1. 失败不计数、验证码不失效 → 4 位数字验证码可被脚本枚举
        // |      （最多 1 万次组合，5 分钟有效期对自动化脚本完全够用）；
        // |   2. 成功才 forget，失败时旧码一直有效。
        // |
        // | 本方法被以下受验证码保护的敏感流程使用：
        // |   setNewPassword（重置密码）/ doSmsLogin（短信登录）
        // |   bindMobile / unBindMobile
        // | 因此这里对失败次数计数，累计 5 次即作废验证码，需重新获取。
        // +----------------------------------------------------------------------
        if (strtolower($verify_code) != strtolower($code)) {
            $fail_key = $verify_key . ':fail_times';
            $fail_times = (int)Cache::get($fail_key, 0) + 1;
            Cache::put($fail_key, $fail_times, 300);
            if ($fail_times >= 5) {
                // 达到上限：作废验证码，调用方必须重新走"获取验证码"流程
                Cache::forget($verify_key);
                Cache::forget($fail_key);
            }
            return false;
        }

        Cache::forget($verify_key);
        Cache::forget($verify_key . ':fail_times');

        return true;
    }


}
