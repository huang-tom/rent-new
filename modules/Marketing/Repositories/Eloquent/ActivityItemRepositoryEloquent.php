<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Marketing\Repositories\Contracts\ActivityItemRepository;
use Modules\Marketing\Repositories\Models\ActivityItem;

/**
 * Class ActivityItemRepositoryEloquent.
 *
 * @package Modules\Marketing\Repositories\Eloquent
 */
class ActivityItemRepositoryEloquent extends BaseRepository implements ActivityItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
