<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use Modules\Account\Repositories\Models\UserDeliveryAddress;

/**
 * Class UserDeliveryAddressRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserDeliveryAddressRepositoryEloquent extends BaseRepository implements UserDeliveryAddressRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserDeliveryAddress::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
