<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductTagCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($product_tag_name = $this->request->get('product_tag_name')) {
            $query->where('product_tag_name', 'like', '%' . $product_tag_name . '%');
        }

    }

    protected function after($model)
    {
        return $model->orderBy('product_tag_sort', 'ASC')->orderBy('product_tag_id', 'ASC');
    }

}
