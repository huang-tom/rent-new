<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderLogRepository;
use Modules\Trade\Repositories\Models\NewOrderLog;

class NewOrderLogRepositoryEloquent extends BaseRepository implements NewOrderLogRepository
{
    public function model()
    {
        return NewOrderLog::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
