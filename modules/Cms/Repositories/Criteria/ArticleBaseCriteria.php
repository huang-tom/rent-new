<?php

namespace Modules\Cms\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ArticleBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($article_title = $this->request->get('article_title')) {
            $query->where('article_title', 'like', "%$article_title%");
        }

        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', '=', $category_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('article_sort', 'ASC')->orderBy('article_id', 'DESC');
    }

}
