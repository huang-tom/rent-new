<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Pay\Repositories\Models\UserResource;

/**
 * Class UserResourceRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class UserResourceRepositoryEloquent extends BaseRepository implements UserResourceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserResource::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
