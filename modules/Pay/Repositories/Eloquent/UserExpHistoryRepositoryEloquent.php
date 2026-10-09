<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\UserExpHistoryRepository;
use Modules\Pay\Repositories\Models\UserExpHistory;

/**
 * Class UserExpHistoryRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class UserExpHistoryRepositoryEloquent extends BaseRepository implements UserExpHistoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserExpHistory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
