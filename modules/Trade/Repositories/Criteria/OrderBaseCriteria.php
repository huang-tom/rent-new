<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //订单状态
        if ($order_state_id = $this->request->get('order_state_id')) {
            $query->where('order_state_id', '=', $order_state_id);
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('order_time', 'DESC');
    }

}
