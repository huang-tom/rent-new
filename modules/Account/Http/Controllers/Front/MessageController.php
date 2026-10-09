<?php

namespace Modules\Account\Http\Controllers\Front;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserMessageCriteria;
use Modules\Account\Services\UserMessageService;
use App\Support\Respond;

class MessageController extends BaseController
{
    private $userMessageService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserMessageService $userMessageService)
    {
        $this->userMessageService = $userMessageService;
        $this->userId = checkLoginUserId();
    }


    /**
     * 用户消息列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $request['message_kind'] = 2;
        $data = $this->userMessageService->list($request, new UserMessageCriteria($request));

        return Respond::success($data);
    }


    public function getMessageNum(Request $request)
    {
        $user_id = $this->userId;
        $data = $this->userMessageService->getMessageNum($user_id);

        return Respond::success($data);
    }


    /**
     * 消息详情
     */
    public function get(Request $request)
    {
        $message_id = $request->get('message_id', 0);
        $data = $this->userMessageService->getOneMessage($message_id, $this->userId);

        return Respond::success($data);
    }


    public function add(Request $request)
    {
        $data = $this->userMessageService->addMessage($request);

        return Respond::success($data);
    }


    /**
     * 获取最近未读消息数量
     */
    public function getMsgCount(Request $request)
    {
        $recently_flag = $request->input('recently_flag', true);
        $user_id = $this->userId;
        $data = $this->userMessageService->getMsgCount($recently_flag, $user_id);

        return Respond::success($data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImConfig(Request $request)
    {
        $user_other_id = $request->get('user_other_id', 0);
        $chat_item_id = $request->get('chat_item_id', 0);
        $chat_order_id = $request->get('chat_order_id', '');

        $data = $this->userMessageService->getImConfig($this->userId, $user_other_id, $chat_item_id, $chat_order_id);

        return Respond::success($data);
    }

    public function setRead(Request $request)
    {
        $data = $this->userMessageService->setRead($request);

        return Respond::success($data);
    }

}
