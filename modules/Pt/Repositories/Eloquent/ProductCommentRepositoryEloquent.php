<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductCommentRepository;
use Modules\Pt\Repositories\Models\ProductComment;

/**
 * Class ProductCommentRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductCommentRepositoryEloquent extends BaseRepository implements ProductCommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductComment::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
