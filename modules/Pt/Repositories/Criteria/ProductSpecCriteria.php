<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductSpecCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //规格名称
        if ($spec_name = $this->request->get('spec_name')) {
            $query->where('spec_name', 'like', '%' . $spec_name . '%');
        }

        //分类ID
        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', '=', $category_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('spec_sort', 'ASC')->orderBy('spec_id', 'ASC');
    }

}
