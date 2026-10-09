<?php

namespace Modules\O2o\Repositories\Eloquent;

use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\O2o\Repositories\Contracts\ChainItemRepository;
use Modules\O2o\Repositories\Models\ChainItem;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class ChainItemRepositoryEloquent.
 *
 * @package Modules\O2o\Repositories\Eloquent
 */
class ChainItemRepositoryEloquent extends BaseRepository implements ChainItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChainItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
