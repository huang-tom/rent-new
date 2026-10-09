<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderRefundRepository;
use Modules\Trade\Repositories\Models\NewOrderRefund;

class NewOrderRefundRepositoryEloquent extends BaseRepository implements NewOrderRefundRepository
{
    public function model()
    {
        return NewOrderRefund::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
