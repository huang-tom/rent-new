<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderRepairRepository;
use Modules\Trade\Repositories\Models\NewRentOrderRepair;

class NewRentOrderRepairRepositoryEloquent extends BaseRepository implements NewRentOrderRepairRepository
{
    public function model()
    {
        return NewRentOrderRepair::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
