<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class PageCategoryNavCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($category_nav_name = $this->request->get('category_nav_name')) {
            $query->where('category_nav_name', 'like', "%$category_nav_name%");
        }

        if ($category_nav_enable = $this->request->get('category_nav_enable')) {
            $query->where('category_nav_enable', '=', $category_nav_enable);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('category_nav_order', 'ASC')->orderBy('category_nav_id', 'ASC');
    }

}
