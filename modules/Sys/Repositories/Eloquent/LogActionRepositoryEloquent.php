<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\LogActionRepository;
use Modules\Sys\Repositories\Models\LogAction;

/**
 * Class LogActionRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class LogActionRepositoryEloquent extends BaseRepository implements LogActionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LogAction::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
