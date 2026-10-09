<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductAssistRepository;
use Modules\Pt\Repositories\Models\ProductAssist;

/**
 * Class ProductAssistRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductAssistRepositoryEloquent extends BaseRepository implements ProductAssistRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductAssist::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
