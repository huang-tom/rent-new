<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use Modules\Marketing\Repositories\Models\ActivityBase;

/**
 * Class ActivityBaseRepositoryEloquent.
 *
 * @package Modules\Marketing\Repositories\Eloquent
 */
class ActivityBaseRepositoryEloquent extends BaseRepository implements ActivityBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
