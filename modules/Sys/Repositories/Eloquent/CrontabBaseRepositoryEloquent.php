<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\CrontabBaseRepository;
use Modules\Sys\Repositories\Models\CrontabBase;

/**
 * Class CrontabBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class CrontabBaseRepositoryEloquent extends BaseRepository implements CrontabBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return CrontabBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
