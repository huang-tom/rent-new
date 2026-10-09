<?php

namespace Modules\O2o\Repositories\Eloquent;

use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\O2o\Repositories\Contracts\ChainCategoryRepository;
use Modules\O2o\Repositories\Models\ChainCategory;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class ChainCategoryRepositoryEloquent.
 *
 * @package Modules\O2o\Repositories\Eloquent
 */
class ChainCategoryRepositoryEloquent extends BaseRepository implements ChainCategoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChainCategory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
