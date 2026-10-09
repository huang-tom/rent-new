<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductBrandRepository;
use Modules\Pt\Repositories\Models\ProductBrand;

/**
 * Class ProductBrandRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductBrandRepositoryEloquent extends BaseRepository implements ProductBrandRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductBrand::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
