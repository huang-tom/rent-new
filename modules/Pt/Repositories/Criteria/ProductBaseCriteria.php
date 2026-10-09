<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //商品名称
        if ($product_name = $this->request->get('product_name')) {
            $query->where('product_name', 'like', '%' . $product_name . '%');
        }

        //商品ID
        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('product_id', 'DESC');
    }

}
