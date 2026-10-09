<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductImageRepository;
use Modules\Pt\Repositories\Models\ProductImage;

/**
 * Class ProductImageRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductImageRepositoryEloquent extends BaseRepository implements ProductImageRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductImage::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


}
