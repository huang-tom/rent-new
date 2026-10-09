<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductRentPriceRepository;
use Modules\Pt\Repositories\Models\NewProductRentPrice;

/**
 * Class NewProductRentPriceRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class NewProductRentPriceRepositoryEloquent extends BaseRepository implements NewProductRentPriceRepository
{
    public function model()
    {
        return NewProductRentPrice::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
