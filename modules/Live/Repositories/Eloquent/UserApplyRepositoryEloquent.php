<?php

namespace Modules\Live\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Live\Repositories\Contracts\UserApplyRepository;
use Modules\Live\Repositories\Models\UserApply;

/**
 * Class UserApplyRepositoryEloquent.
 *
 * @package Modules\Live\Repositories\Eloquent
 */
class UserApplyRepositoryEloquent extends BaseRepository implements UserApplyRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserApply::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
