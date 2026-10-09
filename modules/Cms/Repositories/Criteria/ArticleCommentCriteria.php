<?php

namespace Modules\Cms\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ArticleCommentCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($article_id = $this->request->get('article_id')) {
            $query->where('article_id', '=', $article_id);
        }

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('comment_id', 'DESC');
    }

}
