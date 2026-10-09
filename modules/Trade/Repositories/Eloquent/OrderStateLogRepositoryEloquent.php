<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderStateLogRepository;
use Modules\Trade\Repositories\Models\OrderStateLog;

/**
 * Class OrderStateLogRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderStateLogRepositoryEloquent extends BaseRepository implements OrderStateLogRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderStateLog::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
