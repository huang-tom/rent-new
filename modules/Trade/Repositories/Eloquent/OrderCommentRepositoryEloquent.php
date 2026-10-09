<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderCommentRepository;
use Modules\Trade\Repositories\Models\OrderComment;

/**
 * Class OrderCommentRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderCommentRepositoryEloquent extends BaseRepository implements OrderCommentRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderComment::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
