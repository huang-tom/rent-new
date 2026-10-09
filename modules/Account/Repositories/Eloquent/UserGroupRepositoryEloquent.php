<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserGroupRepository;
use Modules\Account\Repositories\Models\UserGroup;

/**
 * Class UserGroupRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserGroupRepositoryEloquent extends BaseRepository implements UserGroupRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserGroup::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
