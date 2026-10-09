<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderItemRepository;
use Modules\Trade\Repositories\Models\NewOrderItem;

class NewOrderItemRepositoryEloquent extends BaseRepository implements NewOrderItemRepository
{
    public function model()
    {
        return NewOrderItem::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
