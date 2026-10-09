<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Models\ProductBase;

/**
 * Class ProductBaseRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductBaseRepositoryEloquent extends BaseRepository implements ProductBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


}
