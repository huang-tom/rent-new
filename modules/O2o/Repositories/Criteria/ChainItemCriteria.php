<?php

namespace Modules\O2o\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ChainItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        if ($store_id = $this->request->get('store_id')) {
            $query->where('store_id', '=', $store_id);
        }

        if ($chain_id = $this->request->get('chain_id')) {
            $query->where('chain_id', '=', $chain_id);
        }

        if ($item_id = $this->request->get('item_id')) {
            $query->where('item_id', '=', $item_id);
        }

        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

        if ($chain_item_enable = $this->request->get('chain_item_enable')) {
            $query->where('chain_item_enable', '=', $chain_item_enable);
        }

        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', '=', $category_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('chain_item_id', 'DESC');
    }

}
