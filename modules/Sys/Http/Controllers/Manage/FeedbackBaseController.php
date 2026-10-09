<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\FeedbackBaseCriteria;
use Modules\Sys\Services\FeedbackBaseService;

class FeedbackBaseController extends BaseController
{
    private $feedbackBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FeedbackBaseService $feedbackBaseService)
    {
        $this->feedbackBaseService = $feedbackBaseService;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->feedbackBaseService->list($request, new FeedbackBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增反馈工单
     * [新增 2026-09-22] 前端 api/sys/feedbackBase.ts 的 doAdd() 原先指向未注册的路由
     */
    public function add(Request $request)
    {
        $data = $this->feedbackBaseService->addFeedback($request);

        return Respond::success($data);
    }


    /**
     * 编辑反馈工单（不含「回复」，回复走 editAnswer）
     * [新增 2026-09-22] 前端 api/sys/feedbackBase.ts 的 doEdit() 原先指向未注册的路由
     */
    public function edit(Request $request)
    {
        $data = $this->feedbackBaseService->editFeedback($request);

        return Respond::success($data);
    }


    /**
     * 删除
     *
     * [修正 2026-09-22] 前端「单个删除」与「批量删除」用的是同一条路由
     * （api/sys/feedbackBase.ts 第 46 / 54 行 url 相同），批量时 feedback_id 是逗号拼接串。
     * 服务层 FeedbackBaseService::remove() 已重写为「逗号串 / 数组 / 单值」三种入参通吃，
     * 这里保持薄透传即可。
     */
    public function remove(Request $request)
    {
        $data = $this->feedbackBaseService->remove($request['feedback_id']);

        return Respond::success($data);
    }


    /**
     * 回复反馈
     */
    public function editAnswer(Request $request)
    {
        $data = $this->feedbackBaseService->answer($request);

        return Respond::success($data);
    }

}
