<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductCommentHelpfulCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($comment_id = $this->request->get('comment_id')) {
            $query->where('comment_id', '=', $comment_id);
        }

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('comment_helpful_id', 'DESC');
    }

}
