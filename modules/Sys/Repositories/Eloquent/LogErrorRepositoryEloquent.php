<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\LogErrorRepository;
use Modules\Sys\Repositories\Models\LogError;

/**
 * Class LogErrorRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class LogErrorRepositoryEloquent extends BaseRepository implements LogErrorRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LogError::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
