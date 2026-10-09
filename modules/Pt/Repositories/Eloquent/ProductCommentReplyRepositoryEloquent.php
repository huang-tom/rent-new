<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository;
use Modules\Pt\Repositories\Models\ProductCommentReply;

/**
 * Class ProductCommentReplyRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductCommentReplyRepositoryEloquent extends BaseRepository implements ProductCommentReplyRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductCommentReply::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
