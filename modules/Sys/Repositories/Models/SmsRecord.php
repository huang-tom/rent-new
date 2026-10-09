<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SmsRecord.
 *
 * [新增 2026-09-22] 短信发送记录表。
 *
 * 背景（实测，非推测）：
 *   1. 后台「站点设置 → 短信记录」抽屉一直在调 GET /manage/sys/config/smsRecord
 *      （见 admin/src/views/sys/config/components/SmsRecord.vue:150），
 *      但这条路由从未注册、库里也从来没有短信记录表 → 点一次 404 一次。
 *   2. 短信下发链路 Front/CaptchaController::sendMobileVerifyCode()
 *      只把验证码写进 Redis 缓存（VerifyCodeService，5 分钟 TTL），
 *      既不落库也不留痕，事后完全无从追溯「谁给哪个号码发过什么」。
 *
 * 本表用于把每一次下发尝试（成功/失败都记）持久化，供后台查询与对账。
 *
 * @package Modules\Sys\Repositories\Models
 */
class SmsRecord extends Model
{

    protected $table = 'sys_sms_record';
    protected $primaryKey = 'sms_record_id';
    public $timestamps = false;

    protected $guarded = ['sms_record_id'];

    protected $casts = [
        'sms_status' => 'integer',
    ];

    /** 发送状态：未知（网关未返回可判定结果） */
    const STATUS_UNKNOWN = 0;
    /** 发送状态：下发成功 */
    const STATUS_SUCCESS = 1;
    /** 发送状态：下发失败 */
    const STATUS_FAIL = 2;

}
