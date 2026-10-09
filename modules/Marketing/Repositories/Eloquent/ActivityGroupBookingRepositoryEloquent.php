<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Marketing\Repositories\Contracts\ActivityGroupBookingRepository;
use Modules\Marketing\Repositories\Models\ActivityGroupBooking;


/**
 * Class ActivityGroupBookingRepositoryEloquent.
 *
 * @package Modules\Marketing\Repositories\Eloquent
 */
class ActivityGroupBookingRepositoryEloquent extends BaseRepository implements ActivityGroupBookingRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityGroupBooking::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
