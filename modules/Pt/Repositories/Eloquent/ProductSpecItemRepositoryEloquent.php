<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductSpecItemRepository;
use Modules\Pt\Repositories\Models\ProductSpecItem;

/**
 * Class ProductSpecItemRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductSpecItemRepositoryEloquent extends BaseRepository implements ProductSpecItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductSpecItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
