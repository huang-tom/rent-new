<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\FeedbackCategoryRepository;
use Modules\Sys\Repositories\Models\FeedbackCategory;

/**
 * Class FeedbackCategoryRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class FeedbackCategoryRepositoryEloquent extends BaseRepository implements FeedbackCategoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return FeedbackCategory::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
