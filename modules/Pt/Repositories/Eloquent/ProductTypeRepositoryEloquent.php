<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;
use Modules\Pt\Repositories\Models\ProductType;

/**
 * Class ProductTypeRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductTypeRepositoryEloquent extends BaseRepository implements ProductTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductType::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
