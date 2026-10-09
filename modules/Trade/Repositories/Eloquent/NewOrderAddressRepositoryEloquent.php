<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderAddressRepository;
use Modules\Trade\Repositories\Models\NewOrderAddress;

class NewOrderAddressRepositoryEloquent extends BaseRepository implements NewOrderAddressRepository
{
    public function model()
    {
        return NewOrderAddress::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
