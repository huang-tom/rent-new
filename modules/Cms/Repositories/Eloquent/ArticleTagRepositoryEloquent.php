<?php

namespace Modules\Cms\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Cms\Repositories\Contracts\ArticleTagRepository;
use Modules\Cms\Repositories\Models\ArticleTag;

/**
 * Class ArticleTagRepositoryEloquent.
 *
 * @package Modules\Cms\Repositories\Eloquent
 */
class ArticleTagRepositoryEloquent extends BaseRepository implements ArticleTagRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ArticleTag::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
