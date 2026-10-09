<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserBindConnectRepository;
use Modules\Account\Repositories\Models\UserBindConnect;

/**
 * Class UserBindConnectRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserBindConnectRepositoryEloquent extends BaseRepository implements UserBindConnectRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserBindConnect::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
