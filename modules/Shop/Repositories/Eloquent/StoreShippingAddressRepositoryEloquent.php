<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\StoreShippingAddressRepository;
use Modules\Shop\Repositories\Models\StoreShippingAddress;

/**
 * Class StoreShippingAddressRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class StoreShippingAddressRepositoryEloquent extends BaseRepository implements StoreShippingAddressRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StoreShippingAddress::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
