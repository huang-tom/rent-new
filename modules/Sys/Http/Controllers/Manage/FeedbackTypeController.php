<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\FeedbackTypeCriteria;
use Modules\Sys\Repositories\Validators\FeedbackTypeValidator;
use Modules\Sys\Services\FeedbackTypeService;

class FeedbackTypeController extends BaseController
{
    private $feedbackTypeService;
    private $feedbackTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FeedbackTypeService $feedbackTypeService, FeedbackTypeValidator $feedbackTypeValidator)
    {
        $this->feedbackTypeService = $feedbackTypeService;
        $this->feedbackTypeValidator = $feedbackTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->feedbackTypeService->list($request, new FeedbackTypeCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->feedbackTypeValidator->with($request->all())->passesOrFail('create');
        $data = $this->feedbackTypeService->add([
            'feedback_type_name' => $request['feedback_type_name'],   //名称
            'feedback_type_genus' => $request->input('feedback_type_genus', 1), //所属身份
            'feedback_type_enable' => $request->boolean('feedback_type_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $feedback_type_id = $request['feedback_type_id'];
        $this->feedbackTypeValidator->setId($feedback_type_id);
        $this->feedbackTypeValidator->with($request->all())->passesOrFail('update');
        $data = $this->feedbackTypeService->edit($feedback_type_id, [
            'feedback_type_name' => $request['feedback_type_name'],   //名称
            'feedback_type_genus' => $request->input('feedback_type_genus', 1), //所属身份
            'feedback_type_enable' => $request->boolean('feedback_type_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->feedbackTypeService->remove($request['feedback_type_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $feedback_type_id = $request->get('feedback_type_id');
        $state_data = [];

        if ($request->has('feedback_type_enable')) {
            $state_data['feedback_type_enable'] = $request->boolean('feedback_type_enable');
        }

        // 更新状态
        if ($feedback_type_id && !empty($state_data)) {
            $this->feedbackTypeService->edit($feedback_type_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}
