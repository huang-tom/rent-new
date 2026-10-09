<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderStockLockRepository;
use Modules\Trade\Repositories\Models\NewOrderStockLock;

class NewOrderStockLockRepositoryEloquent extends BaseRepository implements NewOrderStockLockRepository
{
    public function model()
    {
        return NewOrderStockLock::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
