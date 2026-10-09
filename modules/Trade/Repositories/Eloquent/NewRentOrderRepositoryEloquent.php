<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderRepository;
use Modules\Trade\Repositories\Models\NewRentOrder;

class NewRentOrderRepositoryEloquent extends BaseRepository implements NewRentOrderRepository
{
    public function model()
    {
        return NewRentOrder::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
