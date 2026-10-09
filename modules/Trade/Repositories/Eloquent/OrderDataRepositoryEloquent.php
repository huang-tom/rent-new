<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderDataRepository;
use Modules\Trade\Repositories\Models\OrderData;

/**
 * Class OrderDataRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderDataRepositoryEloquent extends BaseRepository implements OrderDataRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderData::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
