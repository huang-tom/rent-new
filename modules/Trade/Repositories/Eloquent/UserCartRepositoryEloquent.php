<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\UserCartRepository;
use Modules\Trade\Repositories\Models\UserCart;

/**
 * Class UserCartRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class UserCartRepositoryEloquent extends BaseRepository implements UserCartRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserCart::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
