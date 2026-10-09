<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Marketing\Repositories\Contracts\ActivityGroupBookingHistoryRepository;


use Modules\Marketing\Repositories\Models\ActivityGroupBookingHistory;


/**
 * Class ActivityGroupBookingHistoryRepositoryEloquent.
 *
 * @package Modules\Marketing\Repositories\Eloquent
 */
class ActivityGroupBookingHistoryRepositoryEloquent extends BaseRepository implements ActivityGroupBookingHistoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityGroupBookingHistory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
