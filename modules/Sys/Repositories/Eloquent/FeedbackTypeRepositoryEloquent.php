<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\FeedbackTypeRepository;
use Modules\Sys\Repositories\Models\FeedbackType;

/**
 * Class FeedbackTypeRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class FeedbackTypeRepositoryEloquent extends BaseRepository implements FeedbackTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FeedbackType::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
