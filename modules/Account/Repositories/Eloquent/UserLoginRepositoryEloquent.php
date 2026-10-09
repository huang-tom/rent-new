<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserLoginRepository;
use Modules\Account\Repositories\Models\UserLogin;

/**
 * Class UserLoginRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserLoginRepositoryEloquent extends BaseRepository implements UserLoginRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserLogin::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
