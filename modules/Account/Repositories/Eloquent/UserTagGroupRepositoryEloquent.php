<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserTagGroupRepository;
use Modules\Account\Repositories\Models\UserTagGroup;

/**
 * Class UserTagGroupRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserTagGroupRepositoryEloquent extends BaseRepository implements UserTagGroupRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserTagGroup::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
