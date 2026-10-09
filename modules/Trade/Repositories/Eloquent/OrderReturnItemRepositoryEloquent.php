<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderReturnItemRepository;
use Modules\Trade\Repositories\Models\OrderReturnItem;

/**
 * Class OrderReturnItemRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderReturnItemRepositoryEloquent extends BaseRepository implements OrderReturnItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderReturnItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
