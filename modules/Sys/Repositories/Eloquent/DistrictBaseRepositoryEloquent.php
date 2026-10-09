<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\DistrictBaseRepository;
use Modules\Sys\Repositories\Models\DistrictBase;

/**
 * Class DistrictBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class DistrictBaseRepositoryEloquent extends BaseRepository implements DistrictBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DistrictBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
