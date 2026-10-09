<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderReturnRepository;
use Modules\Trade\Repositories\Models\OrderReturn;

/**
 * Class OrderReturnRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderReturnRepositoryEloquent extends BaseRepository implements OrderReturnRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderReturn::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
