<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductAssistItemRepository;
use Modules\Pt\Repositories\Models\ProductAssistItem;

/**
 * Class ProductAssistItemRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductAssistItemRepositoryEloquent extends BaseRepository implements ProductAssistItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductAssistItem::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
