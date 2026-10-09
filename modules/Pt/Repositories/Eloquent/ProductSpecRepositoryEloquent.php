<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductSpecRepository;
use Modules\Pt\Repositories\Models\ProductSpec;

/**
 * Class ProductSpecRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductSpecRepositoryEloquent extends BaseRepository implements ProductSpecRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductSpec::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
