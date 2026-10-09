<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserDistributionRepository;
use Modules\Account\Repositories\Models\UserDistribution;

/**
 * Class UserDistributionRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserDistributionRepositoryEloquent extends BaseRepository implements UserDistributionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserDistribution::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
