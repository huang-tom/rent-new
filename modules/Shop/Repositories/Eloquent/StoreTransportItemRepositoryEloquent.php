<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\StoreTransportItemRepository;
use Modules\Shop\Repositories\Models\StoreTransportItem;

/**
 * Class StoreTransportItemRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class StoreTransportItemRepositoryEloquent extends BaseRepository implements StoreTransportItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StoreTransportItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
