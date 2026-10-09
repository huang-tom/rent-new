<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;
use Modules\Account\Repositories\Models\UserTagBase;

/**
 * Class UserTagBaseRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserTagBaseRepositoryEloquent extends BaseRepository implements UserTagBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserTagBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
