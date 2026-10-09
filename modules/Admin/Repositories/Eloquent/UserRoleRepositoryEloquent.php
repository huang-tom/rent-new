<?php

namespace Modules\Admin\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;
use Modules\Admin\Repositories\Models\UserRole;

/**
 * Class UserRoleRepositoryEloquent.
 *
 * @package Modules\Admin\Repositories\Eloquent
 */
class UserRoleRepositoryEloquent extends BaseRepository implements UserRoleRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserRole::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'user_role_name' => $request['user_role_name'],   //名称
            'user_role_code' => $request['user_role_code'],   //标识
            'menu_ids' => $request->input('menu_ids', ''), //所属身份
            'user_role_ctime' => getDateTime(), //创建时间
        ];

        return $data;
    }

}
