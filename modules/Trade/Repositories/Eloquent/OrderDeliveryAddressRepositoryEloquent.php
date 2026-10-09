<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository;
use Modules\Trade\Repositories\Models\OrderDeliveryAddress;

/**
 * Class OrderDeliveryAddressRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderDeliveryAddressRepositoryEloquent extends BaseRepository implements OrderDeliveryAddressRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderDeliveryAddress::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
