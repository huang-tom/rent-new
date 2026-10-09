<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ExpressBaseRepository;
use Modules\Sys\Repositories\Models\ExpressBase;

/**
 * Class ExpressBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ExpressBaseRepositoryEloquent extends BaseRepository implements ExpressBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ExpressBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
