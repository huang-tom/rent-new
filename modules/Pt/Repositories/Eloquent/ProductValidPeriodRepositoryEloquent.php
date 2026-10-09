<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductValidPeriodRepository;
use Modules\Pt\Repositories\Models\ProductValidPeriod;

/**
 * Class ProductValidPeriodRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductValidPeriodRepositoryEloquent extends BaseRepository implements ProductValidPeriodRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductValidPeriod::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
