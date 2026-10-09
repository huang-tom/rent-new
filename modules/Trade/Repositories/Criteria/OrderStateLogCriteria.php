<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderStateLogCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', '=', $order_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('order_state_time', 'DESC');
    }

}
