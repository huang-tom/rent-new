<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserFriendRepository;
use Modules\Account\Repositories\Models\UserFriend;

/**
 * Class UserFriendRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserFriendRepositoryEloquent extends BaseRepository implements UserFriendRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserFriend::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
