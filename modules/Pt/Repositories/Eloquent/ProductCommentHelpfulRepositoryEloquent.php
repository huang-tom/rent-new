<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductCommentHelpfulRepository;
use Modules\Pt\Repositories\Models\ProductCommentHelpful;

/**
 * Class ProductCommentHelpfulRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductCommentHelpfulRepositoryEloquent extends BaseRepository implements ProductCommentHelpfulRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductCommentHelpful::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
