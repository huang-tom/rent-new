<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\StoreExpressLogisticsRepository;
use Modules\Shop\Repositories\Models\StoreExpressLogistics;

/**
 * Class StoreExpressLogisticsRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class StoreExpressLogisticsRepositoryEloquent extends BaseRepository implements StoreExpressLogisticsRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StoreExpressLogistics::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
