<?php

namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use Carbon\Carbon;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserMessageRepository;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

/**
 * Class UserMessageService.
 *
 * @package Modules\Account\Services
 */
class UserMessageService extends BaseService
{
    private $userInfoRepository;
    private $configBaseRepository;
    private $productItemRepository;

    public function __construct(
        UserMessageRepository $userMessageRepository,
        UserInfoRepository    $userInfoRepository,
        ConfigBaseRepository  $configBaseRepository,
        ProductItemRepository $productItemRepository
    )
    {
        $this->repository = $userMessageRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->productItemRepository = $productItemRepository;
    }


    public function getNotice($user_id)
    {
        $ten_days_ago = Carbon::now()->subDays(10)->timestamp;
        $data = $this->repository->find([
            ['user_id', '=', $user_id],
            ['message_time', '>', $ten_days_ago * 1000]
        ]);

        return $data;
    }


    /**
     * 列表数据
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        if (!empty($data['data'])) {
            $items = $data['data'];
            $user_other_ids = array_column_unique($items, 'user_other_id');
            $user_ids = array_column_unique($items, 'user_id');
            $user_info_rows = $this->userInfoRepository->gets($user_ids);
            $other_info_rows = $this->userInfoRepository->gets($user_other_ids);

            foreach ($items as $k => $item) {
                $items[$k]['user_avatar'] = '';
                $items[$k]['user_other_avatar'] = '';

                if (isset($user_info_rows[$item['user_id']])) {
                    $items[$k]['user_avatar'] = $user_info_rows[$item['user_id']]['user_avatar'];
                }
                if (isset($other_info_rows[$item['user_other_id']])) {
                    $items[$k]['user_other_avatar'] = $other_info_rows[$item['user_other_id']]['user_avatar'];
                }
            }

            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 消息详情
     * @param $message_id
     * @param $user_id
     * @return array
     * @throws ErrorException
     */
    public function getOneMessage($message_id, $user_id = 0)
    {
        $data = $this->repository->getOne($message_id);
        if ($user_id) {
            if ($user_id != $data['user_id'])
                throw new ErrorException(__('无操作权限'));
        }

        if (!empty($data)) {
            $data['user_avatar'] = '';
            $data['user_other_avatar'] = '';

            $user_info = $this->userInfoRepository->getOne($data['user_id']);
            if (!empty($user_info)) {
                $data['user_avatar'] = $user_info['user_avatar'];
            }

            $other_info = $this->repository->getOne($data['user_other_id']);
            if (!empty($other_info)) {
                $data['user_other_avatar'] = $other_info['user_avatar'];
            }
        }

        return $data;
    }

    public function getMessageNum($user_id)
    {

        $data['unread_number'] = $this->repository->getNum([
            'user_id' => $user_id,
            'message_kind' => 2,
            'message_is_read' => 0
        ]);

        $data['red_number'] = $this->repository->getNum([
            'user_id' => $user_id,
            'message_kind' => 2,
            'message_is_read' => 1
        ]);

        return $data;
    }


    /**
     * 获取未读消息数量
     * @param $recently_flag
     * @param $user_id
     * @return int[]
     */
    public function getMsgCount($recently_flag, $user_id)
    {
        $user_message_res = [
            'num' => 0,
            'red_number' => 0,
            'unread_number' => 0
        ];

        $base_cond = [
            'user_id' => $user_id,
            'message_kind' => 2,
            'message_is_read' => 0
        ];

        if ($recently_flag) {
            $time = time() * 1000; // 将时间戳转换为毫秒
            $base_cond[] = ['message_time', '>', ($time - 60 * 5 * 1000)];
        }

        $res = $this->repository->find($base_cond);
        $user_message_res['num'] = count($res);

        return $user_message_res;
    }


    /**
     * 参照老版本
     * @param $user_id
     * @param $user_other_id
     * @param $chat_item_id
     * @param $chat_order_id
     * @return array
     */
    public function getImConfig($user_id, $user_other_id, $chat_item_id, $chat_order_id)
    {
        $data = [];
        $service_user_id = $this->configBaseRepository->getConfig('service_user_id', 0);
        $im_offline_msg = $this->configBaseRepository->getConfig('im_offline_msg', '');
        $node_site_url = $this->configBaseRepository->getPlantformChatUrl($user_id, $service_user_id);
        $offline_msg = urlencode($im_offline_msg);

        // [本地修复] 必须强转布尔。getConfig() 对非 json/dot 类型不做类型转换，
        // 表中的 im_enable 存的是字符串 "0"，序列化成 JSON 后是 "im_chat":"0"；
        // 前端 admin/im/im.js 判断的是 if (res.data.im_chat) —— 非空字符串在 JS 里是真值，
        // 于是即使后台已关闭 IM，它仍会去加载 layui/layim 并尝试连接厂商的
        // 第三方聊天服务器（wss 非标准端口），连接失败后弹出「操作无法成功，请稍后重试！」
        // 「服务端响应错误！」两个错误框，出现在后台的每一个页面上。
        $im_enable = (bool)$this->configBaseRepository->getConfig('im_enable', false);

        $data['im_chat'] = $im_enable;
        // [本地化] 关闭 IM 时不再下发厂商聊天服务器地址与签名 token，避免把
        // 厂商官方域名和会话 token 暴露给前端（该值仅在 IM 开启时才有意义）。
        $data['node_site_url'] = $im_enable ? ($node_site_url . '&offline_msg=' . $offline_msg) : '';
        $data['suid'] = $service_user_id;
        // [本地化] 原为厂商官方测试站资源路径（且前端 im.js 已固定使用 window.admin_url，
        // 此值不会真正生效）。改为本地后台地址。
        $data['resource_site_url'] = env('URL_ADMIN', '');
        $data['default_image'] = $this->configBaseRepository->getConfig('default_image');

        if ($user_id) {

            $user_info = $this->userInfoRepository->getOne($user_id);
            $user_info['puid'] = $this->configBaseRepository->getPlantformUid($user_id);
            $user_info['suid'] = $service_user_id;
            $data['puid'] = $user_info['puid'];
            $data['user_info'] = $user_info;

            if ($chat_item_id) {
                $data['chat_item_row'] = $this->productItemRepository->getOne($chat_item_id);
            }
        }

        $user_other_info = [];
        if ($user_other_id) {
            $user_other_info = $this->userInfoRepository->getOne($user_other_id);

            $this->repository->editWhere([
                'user_id' => $user_id,
                'user_other_id' => $user_other_id
            ], ['message_is_read' => 1]);
        } else {
            $user_other_info['user_nickname'] = __('访客');
            $user_other_info['user_avatar'] = $data['default_image'];
        }

        $user_other_info['user_id'] = $user_other_id;
        $user_other_info['puid'] = $this->configBaseRepository->getPlantformUid($user_other_id);
        $user_other_info['suid'] = $service_user_id;
        $data['user_other_info'] = $user_other_info;

        //权限判断
        $column_row = [];
        $data_im = app(UserFriendService::class)->getFriendsInfo($column_row, $user_id, 1, 1000);
        $data['group'] = $data_im['group'];
        $data['mine'] = $data_im['mine'];
        $data['friend'] = $data_im['friend'];

        return $data;
    }


    /**
     * 添加短消息
     *
     * @access public
     */
    public function addMessage($request)
    {
        $message_cat = $request->input('type', 'text');
        $user_other_nickname = $request->input('user_nickname', '');

        if ($user_other_nickname) {
            $user_other_row = $this->userInfoRepository->findOne(['user_nickname' => $user_other_nickname]);
        } else {
            $user_other_id = $request->input('user_other_id', 0);
            if (!$user_other_id) {
                //IM
                $to_row = $request->input('to', '');
                if ($to_row) {
                    $to_row = json_decode($to_row, true);
                    $user_other_id = @$to_row['friend_id'];
                    if (!$user_other_id) {
                        $user_other_row = $this->userInfoRepository->findOne(['user_nickname' => $to_row['name']]);
                    }
                }
            }

            if ($user_other_id) {
                $user_other_row = $this->userInfoRepository->getOne($user_other_id);
            }
        }

        if ($message_content = $request->input('message_content', '')) {
            $message_title = $request->input('message_title', '');
        } else {
            $mine_row = $request->input('mine', '');
            if ($mine_row) {
                $mine_row = json_decode($mine_row, true);
                $message_title = '';
                $message_content = $mine_row['content'];
            }
        }

        $userRow = User::getUser();
        $user_id = $userRow->user_id;
        $user_nickname = $userRow->user_nickname;

        if (!empty($user_other_row)) {
            $base_message_row = [
                'message_title' => $message_title, //短消息标题
                'message_content' => $message_content, // 短消息内容
                'message_length' => $request->input('length', 0),
                'message_w' => $request->input('w', 0),
                'message_h' => $request->input('h', 0),
                'message_is_delete' => 0, // 短消息状态(BOOL):0-正常状态;1-删除状态
                'message_type' => 2, // 消息类型(ENUM):1-系统消息;2-用户消息;3-私信
                'message_cat' => $message_cat,
                'message_time' => getTime()
            ];

            //发件人
            $data = $base_message_row;
            $data['user_id'] = $user_id;
            $data['user_nickname'] = $user_nickname;
            $data['user_other_id'] = $user_other_row['user_id'];
            $data['user_other_nickname'] = $user_other_row['user_nickname'];
            $data['message_is_read'] = 1; // 是否读取(BOOL):0-未读;1-已读
            $data['message_kind'] = 1;


            //接收人
            $other = $base_message_row;
            $other['user_id'] = $user_other_row['user_id']; // 短消息接收人
            $other['user_nickname'] = $user_other_row['user_nickname']; // 接收人用户名
            $other['user_other_id'] = $user_id; // 短消息发送人
            $other['user_other_nickname'] = $user_nickname; // 发信息人用户名
            $other['message_is_read'] = 0; // 是否读取(BOOL):0-未读;1-已读
            $other['message_kind'] = 2;//消息种类

            $message_res = $this->repository->add($data);
            if ($message_res) {
                $message_id = $message_res->getKey();
                $data['message_id'] = $message_id;
            } else {
                throw new ErrorException(__('发送失败'));
            }

            $message_other_res = $this->repository->add($other);
            if ($message_other_res) {
                $message_other_id = $message_other_res->getKey();
                $data['message_other_id'] = $message_other_id;
            } else {
                throw new ErrorException(__('发送失败'));
            }

            return $data;
        } else {
            throw new ErrorException(__('接收者不存在'));
        }
    }


    //消息设置已读
    public function setRead($request)
    {
        $isAll = $request->input('isAll', 0);
        $user_id = User::getUserId();
        $edit_row = ['message_is_read' => 1];
        if ($isAll) {
            $data = $this->repository->editWhere(['user_id' => $user_id], $edit_row);
        } else {
            $message_id = $request->input('message_id', 0);
            $data = $this->repository->edit($message_id, $edit_row);
        }

        return $data;
    }

}
