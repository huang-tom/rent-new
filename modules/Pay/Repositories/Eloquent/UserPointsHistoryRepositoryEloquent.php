<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\UserPointsHistoryRepository;
use Modules\Pay\Repositories\Models\UserPointsHistory;

/**
 * Class UserPointsHistoryRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class UserPointsHistoryRepositoryEloquent extends BaseRepository implements UserPointsHistoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserPointsHistory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
