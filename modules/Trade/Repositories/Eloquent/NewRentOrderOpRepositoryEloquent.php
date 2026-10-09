<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderOpRepository;
use Modules\Trade\Repositories\Models\NewRentOrderOp;

class NewRentOrderOpRepositoryEloquent extends BaseRepository implements NewRentOrderOpRepository
{
    public function model()
    {
        return NewRentOrderOp::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
