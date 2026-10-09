<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderAddressRepository;
use Modules\Trade\Repositories\Models\NewRentOrderAddress;

class NewRentOrderAddressRepositoryEloquent extends BaseRepository implements NewRentOrderAddressRepository
{
    public function model()
    {
        return NewRentOrderAddress::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
