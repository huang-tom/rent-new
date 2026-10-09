<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\FeedbackBaseRepository;
use Modules\Sys\Repositories\Models\FeedbackBase;

/**
 * Class FeedbackBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class FeedbackBaseRepositoryEloquent extends BaseRepository implements FeedbackBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FeedbackBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
