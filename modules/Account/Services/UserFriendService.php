<?php

namespace Modules\Account\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserFriendRepository;
use Modules\Account\Repositories\Contracts\UserGroupRelRepository;
use Modules\Account\Repositories\Contracts\UserGroupRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserZoneRelRepository;
use Modules\Account\Repositories\Contracts\UserZoneRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

/**
 * Class UserFriendService.
 *
 * @package Modules\Account\Services
 */
class UserFriendService extends BaseService
{
    private $configBaseRepository;
    private $userInfoRepository;
    private $userGroupRepository;
    private $userGroupRelRepository;
    private $userZoneRepository;
    private $userZoneRelRepository;

    public function __construct(
        ConfigBaseRepository   $configBaseRepository,
        UserFriendRepository   $userFriendRepository,
        UserInfoRepository     $userInfoRepository,
        UserGroupRepository    $userGroupRepository,
        UserGroupRelRepository $userGroupRelRepository,
        UserZoneRepository     $userZoneRepository,
        UserZoneRelRepository  $userZoneRelRepository
    )
    {
        $this->configBaseRepository = $configBaseRepository;
        $this->repository = $userFriendRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->userGroupRelRepository = $userGroupRelRepository;
        $this->userZoneRepository = $userZoneRepository;
        $this->userZoneRelRepository = $userZoneRelRepository;
    }


    /**
     * 参照老版本
     * @param $column_row
     * @param $user_id
     * @param $page
     * @param $rows
     * @return array
     */
    public function getFriendsInfo($column_row = array(), $user_id, $page = 1, $rows = 500)
    {
        $data = array();

        $friend_rows = $this->repository->find(['user_id' => $user_id]);
        $friend_rows = array_column($friend_rows, null, 'friend_id');

        $friend_user_ids = array_column_unique($friend_rows, 'friend_id');
        $friend_info_rows = $this->userInfoRepository->gets($friend_user_ids);

        $username = __("佚名");
        $avatar = $this->configBaseRepository->getConfig('user_no_avatar');
        $account = '';

        foreach ($friend_rows as $friend_id => $friend_row) {
            if (isset($friend_info_rows[$friend_id])) {
                $friend_info = $friend_info_rows[$friend_id];
                $username = $friend_info['user_nickname'];
                $avatar = $friend_info['user_avatar'];
                $account = $friend_info['user_account'];
            }

            $friend_rows[$friend_id]['username'] = $username;
            $friend_rows[$friend_id]['avatar'] = $avatar;
            $friend_rows[$friend_id]['account'] = $account;

            $friend_rows[$friend_id]['id'] = $friend_id;
        }

        //读取用户组
        $group_rows = $this->userGroupRepository->find(['user_id' => $user_id]);
        foreach ($group_rows as $group_id => $group_row) {
            $group_rows[$group_id]['groupname'] = $group_row['group_name'];
        }

        $group_user_ids = array();
        $group_ids = array_column_unique($group_rows, 'group_id');
        if ($group_ids) {
            $group_rel_rows = $this->userGroupRelRepository->find([['group_id', 'IN', $group_ids]]);
            $group_user_ids = array_column_unique($group_rel_rows, 'user_id');
            foreach ($group_rel_rows as $group_rel_row) {
                $group_rows[$group_rel_row['group_id']]['list'][] = $friend_rows[$group_rel_row['user_id']];
            }
        }

        //未分组判断
        $none_group_friend_rows = array();
        foreach ($friend_rows as $friend_id => $friend_row) {
            if (!in_array($friend_id, $group_user_ids)) {
                $none_group_friend_rows[] = $friend_row;
            }
        }

        if ($none_group_friend_rows) {
            $temp = array();
            $temp['list'] = $none_group_friend_rows;
            $temp['groupname'] = __('未分组');
            $temp['id'] = 0;
            $temp['online'] = 0;

            array_unshift($group_rows, $temp);
        }

        //群组
        $user_zone_rel_rows = $this->userZoneRelRepository->find(['user_id' => $user_id]);
        $zone_ids = array_column_unique($user_zone_rel_rows, 'zone_id');
        $zone_rows = $this->userZoneRepository->gets($zone_ids);

        $data['friend'] = $group_rows ? array_values($group_rows) : [];
        $data['group'] = array();

        if ($zone_rows) {
            foreach ($zone_rows as $zone_id => $zone_row) {
                $zone_rows[$zone_id]['avatar'] = "//tva3.sinaimg.cn/crop.64.106.361.361.50/7181dbb3jw8evfbtem8edj20ci0dpq3a.jpg";
                $zone_rows[$zone_id]['groupname'] = $zone_row['zone_name'];
                $zone_rows[$zone_id]['id'] = $this->configBaseRepository->getPlantformUid('zone-' . $zone_row['id']);
            }

            $data['group'] = $zone_rows ? array_values($zone_rows) : [];
        }

        $data['mine'] = $this->userInfoRepository->getOne($user_id);
        if ($data['mine']) {
            $data['mine']['avatar'] = $data['mine']['user_avatar'];
            $data['mine']['username'] = $data['mine']['user_nickname'];
            $data['mine']['status'] = "online";
            $data['mine']['id'] = $this->configBaseRepository->getPlantformUid($data['mine']['user_id']);
        }

        //修正平台Id
        foreach ($data['friend'] as $k => $g_rows) {
            $data['friend'][$k]['id'] = $this->configBaseRepository->getPlantformUid($g_rows['id']);
            if (isset($g_rows['list']) && is_array($g_rows['list'])) {
                foreach ($g_rows['list'] as $index => $f_row) {
                    $data['friend'][$k]['list'][$index]['id'] = $this->configBaseRepository->getPlantformUid($f_row['friend_id']);
                }
            }
        }

        return $data;
    }

}
