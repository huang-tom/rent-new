<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderLogisticsRepository;
use Modules\Trade\Repositories\Models\OrderLogistics;

/**
 * Class OrderLogisticsRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderLogisticsRepositoryEloquent extends BaseRepository implements OrderLogisticsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderLogistics::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
