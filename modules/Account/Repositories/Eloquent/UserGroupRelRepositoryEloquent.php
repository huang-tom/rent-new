<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserGroupRelRepository;
use Modules\Account\Repositories\Models\UserGroupRel;

/**
 * Class UserGroupRelRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserGroupRelRepositoryEloquent extends BaseRepository implements UserGroupRelRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserGroupRel::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
