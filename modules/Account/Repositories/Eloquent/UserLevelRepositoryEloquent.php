<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Account\Repositories\Models\UserLevel;

/**
 * Class UserLevelRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserLevelRepositoryEloquent extends BaseRepository implements UserLevelRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserLevel::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
