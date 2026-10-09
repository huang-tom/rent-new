<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderInvoiceCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //发票抬头
        if ($invoice_title = $this->request->get('invoice_title')) {
            $query->where('invoice_title', 'like', "%$invoice_title%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //发票状态
        if ($invoice_status = $this->request->get('invoice_status')) {
            $query->where('invoice_status', '=', $invoice_status);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('order_invoice_id', 'DESC');
    }

}
