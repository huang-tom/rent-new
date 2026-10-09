<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Marketing\Repositories\Contracts\ActivityTypeRepository;
use Modules\Marketing\Repositories\Models\ActivityType;

/**
 * Class ActivityTypeRepositoryEloquent.
 *
 * @package Modules\Marketing\Repositories\Eloquent
 */
class ActivityTypeRepositoryEloquent extends BaseRepository implements ActivityTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityType::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
