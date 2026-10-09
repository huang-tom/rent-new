<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\DictItemRepository;
use Modules\Sys\Repositories\Models\DictItem;

/**
 * Class DictItemRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class DictItemRepositoryEloquent extends BaseRepository implements DictItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DictItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
