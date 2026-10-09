<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductRentPeriodRepository;
use Modules\Pt\Repositories\Models\NewProductRentPeriod;

/**
 * Class NewProductRentPeriodRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class NewProductRentPeriodRepositoryEloquent extends BaseRepository implements NewProductRentPeriodRepository
{
    public function model()
    {
        return NewProductRentPeriod::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
