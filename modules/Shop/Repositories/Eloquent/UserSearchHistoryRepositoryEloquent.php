<?php

namespace Modules\Shop\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Shop\Repositories\Contracts\UserSearchHistoryRepository;
use Modules\Shop\Repositories\Models\UserSearchHistory;

/**
 * Class UserSearchHistoryRepositoryEloquent.
 *
 * @package Modules\Shop\Repositories\Eloquent
 */
class UserSearchHistoryRepositoryEloquent extends BaseRepository implements UserSearchHistoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserSearchHistory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
