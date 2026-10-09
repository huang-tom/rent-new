<?php

namespace Modules\Cms\Repositories\Eloquent;

use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Cms\Repositories\Contracts\ArticleBaseRepository;
use Modules\Cms\Repositories\Models\ArticleBase;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class ArticleBaseRepositoryEloquent.
 *
 * @package Modules\Cms\Repositories\Eloquent
 */
class ArticleBaseRepositoryEloquent extends BaseRepository implements ArticleBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ArticleBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
