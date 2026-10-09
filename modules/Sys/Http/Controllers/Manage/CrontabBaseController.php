<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\CrontabBaseCriteria;
use Modules\Sys\Services\CrontabBaseService;

class CrontabBaseController extends BaseController
{
    private $crontabBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CrontabBaseService $crontabBaseService)
    {
        $this->crontabBaseService = $crontabBaseService;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->crontabBaseService->list($request, new CrontabBaseCriteria($request));

        return Respond::success($data);
    }

    /**
     * 新增
     * [新增 2026-09-22] 前端 api/sys/crontabBase.ts 的 doAdd() 一直指向这条路由，原先未注册
     */
    public function add(Request $request)
    {
        $data = $this->crontabBaseService->addCrontab($request);

        return Respond::success($data);
    }


    /**
     * 删除（支持单个 / 批量）
     * [新增 2026-09-22] 同上，doRemove() 一直指向这条路由，原先未注册
     */
    public function remove(Request $request)
    {
        $data = $this->crontabBaseService->removeCrontab($request);

        return Respond::success($data);
    }


    public function edit(Request $request)
    {
        $crontab_id = $request['crontab_id'];
        $data = $this->crontabBaseService->edit($crontab_id, [
            'crontab_minute' => $request->input('crontab_minute', '*'),
            'crontab_hour' => $request->input('crontab_hour', '*'),
            'crontab_day' => $request->input('crontab_day', '*'),
            'crontab_month' => $request->input('crontab_month', '*'),
            // [修正 2026-09-22] 原默认值是 '?'（无效的 cron 字段），漏传时会往库里写一个
            // 非法周期。改为 '*'（与其余四个字段一致）。
            'crontab_week' => $request->input('crontab_week', '*'),
            'crontab_enable' => $request->boolean('crontab_enable', false),
            // [修正 2026-09-22] 原代码是：
            //     'crontab_buildin' => $request->boolean('crontab_last_exe_time', false),
            // 明显是复制粘贴写错了字段名 —— 读的是 crontab_last_exe_time，写的却是
            // crontab_buildin，而编辑表单从来不提交这两个键，于是每次编辑都会把
            // 「是否内置任务」静默清成 0。内置标记一旦丢失，removeCrontab() 的保护就失效了。
            // 现在只在请求显式带上 crontab_buildin 时才改写它。
            'crontab_remark' => $request->input('crontab_remark', '')
        ]);

        if ($request->has('crontab_buildin')) {
            $data = $this->crontabBaseService->edit($crontab_id, [
                'crontab_buildin' => $request->boolean('crontab_buildin'),
            ]);
        }

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $crontab_id = $request->get('crontab_id');
        $state_data = [];

        if ($request->has('crontab_enable')) {
            $state_data['crontab_enable'] = $request->boolean('crontab_enable');
        }

        if ($request->has('crontab_buildin')) {
            $state_data['crontab_buildin'] = $request->boolean('crontab_buildin');
        }

        // 更新状态
        if ($crontab_id && !empty($state_data)) {
            $this->crontabBaseService->edit($crontab_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}
