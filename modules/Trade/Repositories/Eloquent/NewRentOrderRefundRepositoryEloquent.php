<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderRefundRepository;
use Modules\Trade\Repositories\Models\NewRentOrderRefund;

class NewRentOrderRefundRepositoryEloquent extends BaseRepository implements NewRentOrderRefundRepository
{
    public function model()
    {
        return NewRentOrderRefund::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
