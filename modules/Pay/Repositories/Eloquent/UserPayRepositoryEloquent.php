<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\UserPayRepository;
use Modules\Pay\Repositories\Models\UserPay;

/**
 * Class UserPayRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class UserPayRepositoryEloquent extends BaseRepository implements UserPayRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserPay::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
