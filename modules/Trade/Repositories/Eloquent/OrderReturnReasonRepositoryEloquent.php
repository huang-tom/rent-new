<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderReturnReasonRepository;
use Modules\Trade\Repositories\Models\OrderReturnReason;

/**
 * Class OrderReturnReasonRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderReturnReasonRepositoryEloquent extends BaseRepository implements OrderReturnReasonRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderReturnReason::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
