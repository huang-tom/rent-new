<?php

namespace Modules\Invoicing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class StockBillItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //stock_bill_id
        if ($stock_bill_id = $this->request->get('stock_bill_id')) {
            $query->where('stock_bill_id', '=', $stock_bill_id);
        }

        //product_name
        if ($product_name = $this->request->get('product_name')) {
            $query->where('product_name', 'like', "%$product_name%");
        }

        //product_id
        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

        //item_id
        if ($item_id = $this->request->get('item_id')) {
            $query->where('item_id', '=', $item_id);
        }

        //order_id
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('stock_bill_item_id', 'DESC');
    }
}
