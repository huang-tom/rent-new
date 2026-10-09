<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserZoneRepository;
use Modules\Account\Repositories\Models\UserZone;

/**
 * Class UserZoneRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserZoneRepositoryEloquent extends BaseRepository implements UserZoneRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserZone::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
