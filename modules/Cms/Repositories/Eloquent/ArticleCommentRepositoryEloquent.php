<?php

namespace Modules\Cms\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Cms\Repositories\Contracts\ArticleCommentRepository;
use Modules\Cms\Repositories\Models\ArticleComment;

/**
 * Class ArticleCommentRepositoryEloquent.
 *
 * @package Modules\Cms\Repositories\Eloquent
 */
class ArticleCommentRepositoryEloquent extends BaseRepository implements ArticleCommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ArticleComment::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
