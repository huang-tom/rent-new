<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserMessageCriteria;
use Modules\Account\Repositories\Models\User;
use Modules\Account\Services\UserMessageService;

class UserMessageController extends BaseController
{

    private $userMessageService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserMessageService $userMessageService)
    {
        $this->userMessageService = $userMessageService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userMessageService->list($request, new UserMessageCriteria($request));

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $message_id = $request->get('message_id', -1);
        $state_data = [];

        if ($request->has('message_is_read')) {
            $state_data['message_is_read'] = $request->boolean('message_is_read');
        }

        if ($request->has('message_is_delete')) {
            $state_data['message_is_delete'] = $request->boolean('message_is_delete');
        }

        // 更新状态
        if ($message_id && !empty($state_data)) {
            $result = $this->userMessageService->edit($message_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($result);
    }


    public function getNotice(Request $request)
    {
        $user_id = User::getUserId();
        $data = $this->userMessageService->getNotice($user_id);

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     *
     * @param $request
     * @return array
     */
    private function formatRequest($request)
    {
        return [
            'message_parent_id' => $request->input('message_parent_id', 0), //上级编号
            'user_id' => $request->input('user_id', 0), //所属用户
            'user_nickname' => $request->input('user_nickname', ''), //用户昵称
            'message_kind' => $request->input('message_kind', 0), //消息种类
            'user_other_id' => $request->input('user_other_id', 0), //相关用户
            'user_other_nickname' => $request->input('user_other_nickname', ''), //相关昵称
            'message_title' => $request->input('message_title', ''), //消息标题
            'message_content' => $request->input('message_content', ''), //消息内容
            'message_is_read' => $request->boolean('message_is_read'), //是否读取
            'message_is_delete' => $request->boolean('message_is_delete'), //是否删除
            'message_type' => $request->input('message_type', 0), //消息类型
            'message_cat' => $request->input('message_cat', 0), //消息分类
            'message_data_type' => $request->input('message_data_type', 0), //消息数据类型
            'message_data_id' => $request->input('message_data_id', 0), //消息数据编号
            'message_length' => $request->input('message_length', 0), //消息长度
            'message_w' => $request->input('message_w', 0), //图片宽度
            'message_h' => $request->input('message_h', 0), //图片高度
        ];
    }


    /**
     * 新增
     *
     * [新增 2026-09-23] 路由 `/manage/account/userMessage/add` 早已声明，控制器里却没有 add 方法
     * → 静默 404（Lumen 对方法缺失抛 NotFoundHttpException）。
     * 页面在：「系统管理 → 用户消息」（menu_id 4373），工具栏「添加」按钮。
     */
    public function add(Request $request)
    {
        if (!$request->input('user_id')) {
            return Respond::error(__('请先选择所属用户'));
        }

        $add_row = $this->formatRequest($request);
        $add_row['message_time'] = $request->input('message_time') ?: getDateTime();

        $data = $this->userMessageService->add($add_row);

        return Respond::success($data);
    }


    /**
     * 修改
     *
     * [新增 2026-09-23] 同上，补 `/manage/account/userMessage/edit`（原为静默 404）。
     */
    public function edit(Request $request)
    {
        $message_id = (int)$request->input('message_id', 0);
        if (!$message_id) {
            return Respond::error(__('消息编号不能为空'));
        }

        $row = $this->userMessageService->get($message_id);
        if (empty($row)) {
            return Respond::error(__('消息不存在'));
        }

        $data = $this->userMessageService->edit($message_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     *
     * [新增 2026-09-23] 同上，补 `/manage/account/userMessage/remove`（原为静默 404）。
     */
    public function remove(Request $request)
    {
        $message_id = (int)$request->input('message_id', 0);
        if (!$message_id) {
            return Respond::error(__('消息编号不能为空'));
        }

        $data = $this->userMessageService->remove($message_id);

        return Respond::success($data);
    }

}
