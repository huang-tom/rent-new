<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductAssistIndexRepository;
use Modules\Pt\Repositories\Models\ProductAssistIndex;

/**
 * Class ProductAssistIndexRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductAssistIndexRepositoryEloquent extends BaseRepository implements ProductAssistIndexRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductAssistIndex::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
