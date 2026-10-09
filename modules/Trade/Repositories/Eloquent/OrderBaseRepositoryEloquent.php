<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderBaseRepository;
use Modules\Trade\Repositories\Models\OrderBase;

/**
 * Class OrderBaseRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderBaseRepositoryEloquent extends BaseRepository implements OrderBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
