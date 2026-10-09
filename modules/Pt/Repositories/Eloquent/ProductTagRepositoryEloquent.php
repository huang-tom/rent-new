<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductTagRepository;
use Modules\Pt\Repositories\Models\ProductTag;

/**
 * Class ProductTagRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductTagRepositoryEloquent extends BaseRepository implements ProductTagRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductTag::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
