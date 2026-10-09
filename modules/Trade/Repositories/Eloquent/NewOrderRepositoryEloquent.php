<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderRepository;
use Modules\Trade\Repositories\Models\NewOrder;

class NewOrderRepositoryEloquent extends BaseRepository implements NewOrderRepository
{
    public function model()
    {
        return NewOrder::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
