<?php

namespace Modules\Sys\Services;

use Modules\Account\Repositories\Models\User;
use Modules\Sys\Repositories\Models\ConfigBase;
use Modules\Sys\Repositories\Models\SmsRecord;

/**
 * Class SmsRecordService.
 *
 * [新增 2026-09-22] 短信发送记录。
 *
 * 不继承混淆的 Kuteshop\Core\Service\BaseService：
 *   BaseService 的构造函数是 `__construct($repository)`（必填），且其 add/remove/edit
 *   全部走被混淆的 BaseRepository。本服务只需要对一张表做「写一条 / 分页读」，用显式
 *   Model 查询表达更可控。同模块的 VerifyCodeService 也是不继承 BaseService 的普通类。
 *
 * @package Modules\Sys\Services
 */
class SmsRecordService
{

    /**
     * 记录一次短信下发尝试。
     *
     * ⚠️ 该方法是「旁路记录」：任何异常都被吞掉，绝不允许因为写日志失败
     *    而让短信主流程（尤其是登录/重置密码用的验证码下发）失败。
     *
     * @param array $row
     * @return SmsRecord|null
     */
    public function write(array $row)
    {
        try {
            return SmsRecord::create([
                'user_id'         => (int)($row['user_id'] ?? 0),
                'sms_mobile'      => (string)($row['sms_mobile'] ?? ''),
                'sms_content'     => mb_substr((string)($row['sms_content'] ?? ''), 0, 500),
                'sms_scene'       => (string)($row['sms_scene'] ?? ''),
                'sms_gateway_msg' => mb_substr((string)($row['sms_gateway_msg'] ?? ''), 0, 255),
                'sms_status'      => (int)($row['sms_status'] ?? SmsRecord::STATUS_UNKNOWN),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }


    /**
     * 按手机号反查会员编号。
     *
     * 验证码下发是「未登录」接口（/front/sys/captcha/mobile 无 auth 中间件），
     * 请求里只有手机号。此处做一次尽力而为的匹配：
     *   - 命中 → 记录真实 user_id，后台短信记录里能直接定位到会员；
     *   - 未命中（比如新手机号注册）→ 返回 0，属正常情况，不算错误。
     *
     * @param string $mobile
     * @return int
     */
    public function matchUserId($mobile)
    {
        if (empty($mobile)) {
            return 0;
        }

        try {
            return (int)User::where('user_mobile', $mobile)->value('user_id');
        } catch (\Throwable $e) {
            return 0;
        }
    }


    /**
     * 后台短信记录分页列表
     *
     * 前端契约（admin/src/views/sys/config/components/SmsRecord.vue:148-155）：
     *   getSmsRecord({ page, rows }) → { data: { items, records, user_sms_num } }
     * 表格列使用 sms_record_id / user_id / sms_mobile / sms_content / sms_date / sms_time。
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function list($request)
    {
        $page = max(1, (int)$request->input('page', 1));
        // 前端分页参数用 rows（不是 size），这里两个都认，避免再次踩坑
        $size = (int)$request->input('rows', $request->input('size', 10));
        $size = ($size > 0 && $size <= 200) ? $size : 10;

        $query = SmsRecord::query();

        if ($mobile = $request->input('sms_mobile')) {
            $query->where('sms_mobile', 'like', '%' . $mobile . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', (int)$request->input('user_id'));
        }
        if ($request->filled('sms_status')) {
            $query->where('sms_status', (int)$request->input('sms_status'));
        }

        $records = (clone $query)->count();
        $rows = $query->orderBy('sms_record_id', 'DESC')
            ->forPage($page, $size)
            ->get()
            ->toArray();

        // 表格同时用 sms_date（yyyy-MM-dd）和 sms_time（yyyy-MM-dd hh:mm:ss）两个格式化器，
        // 二者都基于同一个字段。这里把同一个值同时挂到两个键上，前端 new Date(...) 均可解析。
        foreach ($rows as $k => $row) {
            $rows[$k]['sms_date'] = $row['sms_time'];
        }

        return [
            'items'        => $rows,
            'records'      => $records,
            'page'         => $page,
            'size'         => $size,
            'user_sms_num' => $this->surplus(),
        ];
    }


    /**
     * 可用短信条数。
     *
     * ⚠️ 口径说明（不要误读成真实网关余额）：
     *   原厂这个数字来自 shopsuite 厂商短信账号的余额接口。本项目已本地化、
     *   不再依赖厂商账号（见 CaptchaController 里的本地化改造），而短信网关是
     *   可替换的（sys_config_base.sms_gateway_url），各网关余额接口并不统一，
     *   因此无法自动同步。
     *   这里读取站点配置 sms_surplus_num（运维按实际充值/网关后台手工维护），
     *   未配置时返回 0，表示"未知/未维护"，而不是"已用完"。
     *
     * @return int
     */
    public function surplus()
    {
        try {
            $value = ConfigBase::query()->where('config_key', 'sms_surplus_num')->value('config_value');
            return (int)$value;
        } catch (\Throwable $e) {
            return 0;
        }
    }

}
