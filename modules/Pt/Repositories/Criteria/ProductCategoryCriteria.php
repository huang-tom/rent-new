<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductCategoryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //父级编号
        if ($category_parent_id = $this->request->get('category_parent_id')) {
            if ($category_parent_id == -1) {
                $category_parent_id = 0;
            }
            $query->where('category_parent_id', '=', $category_parent_id);
        }

        //是否启用
        if ($category_is_enable = $this->request->get('category_is_enable')) {
            $query->where('category_is_enable', '=', $category_is_enable);
        }

        //是否启用
        if ($category_name = $this->request->get('category_name')) {
            $query->where('category_name', 'like', "%$category_name%");
        }

    }

    protected function after($model)
    {
        return $model->orderBy('category_sort', 'ASC')->orderBy('category_id', 'ASC');
    }

}
