<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //商品SKU
        if ($item_id = $this->request->get('item_id')) {
            $query->where('item_id', '=', $item_id);
        }

        //SKU名称
        if ($item_name = $this->request->get('item_name')) {
            $query->where('item_name', 'like', '%' . $item_name . '%');
        }

        //商品ID
        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

        //商品ID
        if ($product_ids = $this->request->get('product_ids')) {
            $query->whereIn('product_id', $product_ids);
        }

        //商品SKU状态
        if ($item_enable = $this->request->get('item_enable')) {
            $query->where('item_enable', '=', $item_enable);
        }

        //商品item_ids
        $item_ids = $this->request->get('item_ids');
        if ($item_ids && !empty(array_filter($item_ids))) {
            $query->whereIn('item_id', $item_ids);
        }

        //stock_warning
        if ($stock_warning = $this->request->get('stock_warning')) {
            $query->where('item_quantity', '<=', $stock_warning);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('item_id', 'DESC');
    }

}
