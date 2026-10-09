<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\CrontabBaseRepository;
use Modules\Sys\Repositories\Models\CrontabBase;

/**
 * Class CrontabBaseService.
 *
 * @package Modules\Sys\Services
 */
class CrontabBaseService extends BaseService
{
    public function __construct(CrontabBaseRepository $crontabBaseRepository)
    {
        $this->repository = $crontabBaseRepository;
    }


    /**
     * 新增计划任务
     *
     * [新增 2026-09-22] 补齐 POST /manage/sys/crontabBase/add。
     * 前端 api/sys/crontabBase.ts 导出 doAdd()，编辑弹窗
     * （views/sys/crontabBase/components/CrontabBaseEdit.vue:102）在「非编辑态」会调它，
     * 但后端从未注册该路由，也从未有对应方法。原厂模板把「添加」按钮注释掉了
     * （views/sys/crontabBase/index.vue 工具栏只剩说明文字），所以这是一条
     * 「代码里调得到、界面上点不到」的断链 —— 一旦把按钮放出来就是 404。
     *
     * 用显式 Model 写入而不是继承来的 add()：继承链会走被混淆的 BaseRepository，
     * 且 formatData 的字段过滤规则不透明；这里字段少、语义明确，显式写更可控。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addCrontab($request)
    {
        $crontab_name = trim((string)$request->input('crontab_name', ''));
        $crontab_file = trim((string)$request->input('crontab_file', ''));

        if ($crontab_name === '') {
            throw new ErrorException(__('请输入任务名称'));
        }
        if ($crontab_file === '') {
            throw new ErrorException(__('请输入任务脚本'));
        }
        // crontab_name / crontab_file 在库里都是 varchar(50)，先挡住超长再落库，
        // 否则严格模式下会抛 SQL 错误（Data too long）而不是给人话。
        if (mb_strlen($crontab_name) > 50) {
            throw new ErrorException(__('任务名称不能超过 50 个字符'));
        }
        if (mb_strlen($crontab_file) > 50) {
            throw new ErrorException(__('任务脚本不能超过 50 个字符'));
        }

        // 同名任务不允许重复（crontab_name 在库里没有唯一索引，只能在此拦）
        if (CrontabBase::where('crontab_name', $crontab_name)->exists()) {
            throw new ErrorException(__('任务名称已存在'));
        }

        $row = [
            'crontab_name'          => $crontab_name,
            'crontab_file'          => $crontab_file,
            'crontab_minute'        => (string)$request->input('crontab_minute', '*'),
            'crontab_hour'          => (string)$request->input('crontab_hour', '*'),
            'crontab_day'           => (string)$request->input('crontab_day', '*'),
            'crontab_month'         => (string)$request->input('crontab_month', '*'),
            'crontab_week'          => (string)$request->input('crontab_week', '*'),
            'crontab_enable'        => $request->boolean('crontab_enable', false),
            // 后台新建的永远是「自定义任务」；crontab_buildin 只由安装脚本/升级脚本设置
            'crontab_buildin'       => false,
            'crontab_remark'        => (string)$request->input('crontab_remark', ''),
            'crontab_last_exe_time' => 0,
            'crontab_next_exe_time' => 0,
        ];

        $crontab = CrontabBase::create($row);
        if (!$crontab) {
            throw new ErrorException(__('操作失败'));
        }

        return $crontab->toArray();
    }


    /**
     * 删除计划任务（支持单个 / 批量）
     *
     * [新增 2026-09-22] 补齐 POST /manage/sys/crontabBase/remove。
     * 与 add 同样属于「代码里调得到、界面上点不到」的断链。
     *
     * 两条硬约束：
     *   1. 内置任务（crontab_buildin = 1）不允许删除 —— 那是系统运行依赖的定时作业，
     *      删掉不会报错，只会让订单状态之类的后台流程悄悄停摆；
     *   2. 入参兼容逗号串与数组两种形态（前端批量删除普遍是
     *      selectRows.map(i => i.id).join()，见 admin/src/views/sys/districtBase/index.vue:172）。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeCrontab($request)
    {
        $raw = $request->input('crontab_id');
        $ids = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $buildin = CrontabBase::whereIn('crontab_id', $ids)->where('crontab_buildin', true)->count();
        if ($buildin > 0) {
            throw new ErrorException(sprintf(__('选中的任务中有 %d 项为系统内置任务，不允许删除'), $buildin));
        }

        $deleted = CrontabBase::whereIn('crontab_id', $ids)->delete();
        if ($deleted > 0) {
            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}
