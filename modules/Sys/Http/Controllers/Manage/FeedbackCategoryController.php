<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\FeedbackCategoryCriteria;
use Modules\Sys\Repositories\Validators\FeedbackCategoryValidator;
use Modules\Sys\Services\FeedbackCategoryService;

class FeedbackCategoryController extends BaseController
{
    private $feedbackCategoryService;
    private $feedbackCategoryValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FeedbackCategoryService $feedbackCategoryService, FeedbackCategoryValidator $feedbackCategoryValidator)
    {
        $this->feedbackCategoryService = $feedbackCategoryService;
        $this->feedbackCategoryValidator = $feedbackCategoryValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->feedbackCategoryService->list($request, new FeedbackCategoryCriteria($request));

        return Respond::success($data);
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->feedbackCategoryValidator->with($request->all())->passesOrFail('create');
        $data = $this->feedbackCategoryService->add([
            'feedback_category_name' => $request['feedback_category_name'],   //名称
            'feedback_type_id' => $request->input('feedback_type_id', 0), //类型编号
            'feedback_category_enable' => $request->boolean('feedback_category_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $feedback_category_id = $request['feedback_category_id'];
        $this->feedbackCategoryValidator->setId($feedback_category_id);
        $this->feedbackCategoryValidator->with($request->all())->passesOrFail('update');
        $data = $this->feedbackCategoryService->edit($feedback_category_id, [
            'feedback_category_name' => $request['feedback_category_name'],   //名称
            'feedback_type_id' => $request->input('feedback_type_id', 0), //类型编号
            'feedback_category_enable' => $request->boolean('feedback_category_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->feedbackCategoryService->remove($request['feedback_category_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $feedback_category_id = $request->get('feedback_category_id');
        $state_data = [];

        if ($request->has('feedback_category_enable')) {
            $state_data['feedback_category_enable'] = $request->boolean('feedback_category_enable');
        }

        // 更新状态
        if ($feedback_category_id && !empty($state_data)) {
            $result = $this->feedbackCategoryService->edit($feedback_category_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($result);
    }

}
