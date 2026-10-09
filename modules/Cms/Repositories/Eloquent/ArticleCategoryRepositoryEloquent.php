<?php

namespace Modules\Cms\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Cms\Repositories\Contracts\ArticleCategoryRepository;
use Modules\Cms\Repositories\Models\ArticleCategory;

/**
 * Class ArticleCategoryRepositoryEloquent.
 *
 * @package Modules\Cms\Repositories\Eloquent
 */
class ArticleCategoryRepositoryEloquent extends BaseRepository implements ArticleCategoryRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ArticleCategory::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
