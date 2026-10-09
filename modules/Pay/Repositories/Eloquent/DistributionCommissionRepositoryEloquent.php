<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\DistributionCommissionRepository;
use Modules\Pay\Repositories\Models\DistributionCommission;

/**
 * Class DistributionCommissionRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class DistributionCommissionRepositoryEloquent extends BaseRepository implements DistributionCommissionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DistributionCommission::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
