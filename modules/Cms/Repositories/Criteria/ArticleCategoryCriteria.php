<?php

namespace Modules\Cms\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ArticleCategoryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($category_name = $this->request->get('category_name')) {
            $query->where('category_name', 'like', "%$category_name%");
        }

        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', '=', $category_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('category_order', 'ASC')->orderBy('category_id', 'ASC');
    }

}
