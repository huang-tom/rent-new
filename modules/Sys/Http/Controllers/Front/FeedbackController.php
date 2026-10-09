<?php

namespace Modules\Sys\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Repositories\Criteria\FeedbackBaseCriteria;
use Modules\Sys\Services\FeedbackBaseService;

class FeedbackController extends BaseController
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
     * 反馈类型分类列表
     */
    public function getCategory()
    {
        $data = $this->feedbackBaseService->getCategory();

        return Respond::success($data);
    }


    /**
     * 反馈列表
     */
    public function list(Request $request)
    {
        $user_id = User::getUserId();
        $request['user_id'] = $user_id;
        $data = $this->feedbackBaseService->list($request, new FeedbackBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $user_id = checkLoginUserId();
        $user = User::getUser();
        $data = $this->feedbackBaseService->add([
            'feedback_category_id' => $request['feedback_category_id'],   //分类编号
            'user_id' => $user_id,   //用户编号
            'user_nickname' => $user['user_nickname'], //用户昵称
            'feedback_question' => $request->input('feedback_question', ''), //反馈问题:在这里描述您遇到的问题
            'feedback_question_url' => $request->input('feedback_question_url', ''), //页面链接
            'feedback_question_time' => getDateTime(), //反馈时间
            'feedback_question_status' => $request->boolean('feedback_question_status'), //举报状态(BOOL):0-未处理;1-已处理
            'item_id' => $request->input('item_id', 0)
        ]);

        return Respond::success($data);
    }


}
