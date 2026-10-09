<?php

namespace Modules\Pt\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Models\ProductIndex;

/**
 * Class ProductIndexRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductIndexRepositoryEloquent extends BaseRepository implements ProductIndexRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductIndex::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
