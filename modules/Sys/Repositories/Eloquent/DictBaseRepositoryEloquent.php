<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\DictBaseRepository;
use Modules\Sys\Repositories\Models\DictBase;

/**
 * Class DictBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class DictBaseRepositoryEloquent extends BaseRepository implements DictBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DictBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
