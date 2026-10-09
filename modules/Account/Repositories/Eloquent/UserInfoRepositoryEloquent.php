<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Models\UserInfo;

/**
 * Class UserInfoRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserInfoRepositoryEloquent extends BaseRepository implements UserInfoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserInfo::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 绑定用户数据
     *
     * @param array $data 数据列表
     * @param array $map 字段映射 [目标字段 => 用户数据字段]
     * @param string $key_name 用户标识字段名称（默认为 "user_id"）
     * @return array 处理后的数据
     */
    public function fixUserInfo(array $data, array $map = ["user_nickname" => "user_nickname"], string $key_name = "user_id"): array
    {
        if (empty($data)) {
            return $data;
        }

        // 获取用户信息
        $user_ids = array_unique(array_column($data, $key_name));
        $exist_user_ids = array_column_unique($data, 'user_id');
        if ($key_name != 'user_id' && !empty($exist_user_ids)) {
            $user_ids = array_merge($exist_user_ids, $user_ids);
        }
        $user_info_rows = $this->gets($user_ids);

        // 遍历数据并绑定用户信息
        foreach ($data as $key => $item) {
            if (!empty($item[$key_name]) && isset($user_info_rows[$item[$key_name]])) {
                foreach ($map as $target_field => $source_field) {
                    $data[$key][$target_field] = $user_info_rows[$item[$key_name]][$source_field] ?? null;
                }
            }
            if ($key_name != 'user_id' && isset($item['user_id']) && isset($user_info_rows[$item['user_id']])) {
                $data[$key]['user_nickname'] = $user_info_rows[$item['user_id']]['user_nickname'];
            }
        }

        return $data;
    }

}
