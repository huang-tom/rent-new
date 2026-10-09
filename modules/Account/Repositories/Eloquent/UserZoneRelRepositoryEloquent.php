<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserZoneRelRepository;
use Modules\Account\Repositories\Models\UserZoneRel;

/**
 * Class UserZoneRelRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserZoneRelRepositoryEloquent extends BaseRepository implements UserZoneRelRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserZoneRel::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
