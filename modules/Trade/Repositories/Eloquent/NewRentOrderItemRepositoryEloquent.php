<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderItemRepository;
use Modules\Trade\Repositories\Models\NewRentOrderItem;

class NewRentOrderItemRepositoryEloquent extends BaseRepository implements NewRentOrderItemRepository
{
    public function model()
    {
        return NewRentOrderItem::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
