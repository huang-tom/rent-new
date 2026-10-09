<?php

namespace Modules\Admin\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Models\UserAdmin;

/**
 * Class UserAdminRepositoryEloquent.
 *
 * @package Modules\Admin\Repositories\Eloquent
 */
class UserAdminRepositoryEloquent extends BaseRepository implements UserAdminRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserAdmin::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
