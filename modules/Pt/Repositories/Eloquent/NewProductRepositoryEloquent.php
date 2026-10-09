<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\NewProductRepository;
use Modules\Pt\Repositories\Models\NewProduct;

/**
 * Class NewProductRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class NewProductRepositoryEloquent extends BaseRepository implements NewProductRepository
{
    public function model()
    {
        return NewProduct::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
