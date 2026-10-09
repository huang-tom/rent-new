<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderLogRepository;
use Modules\Trade\Repositories\Models\NewRentOrderLog;

class NewRentOrderLogRepositoryEloquent extends BaseRepository implements NewRentOrderLogRepository
{
    public function model()
    {
        return NewRentOrderLog::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
