<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Models\ProductInfo;

/**
 * Class ProductInfoRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductInfoRepositoryEloquent extends BaseRepository implements ProductInfoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductInfo::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
