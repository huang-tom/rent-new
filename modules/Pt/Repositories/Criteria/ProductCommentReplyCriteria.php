<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductCommentReplyCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($comment_id = $this->request->get('comment_id')) {
            $query->where('comment_id', '=', $comment_id);
        }

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        if ($user_name = $this->request->get('user_name')) {
            $query->where('user_name', 'like', '%' . $user_name . '%');
        }

        if ($user_name_to = $this->request->get('user_name_to')) {
            $query->where('user_name_to', 'like', '%' . $user_name_to . '%');
        }

        if ($comment_reply_content = $this->request->get('comment_reply_content')) {
            $query->where('comment_reply_content', 'like', '%' . $comment_reply_content . '%');
        }

    }

    protected function after($model)
    {
        return $model->orderBy('comment_reply_id', 'DESC');
    }

}
