<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderReturnCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //退单状态
        if ($return_state_id = $this->request->get('return_state_id')) {
            $query->where('return_state_id', '=', $return_state_id);
        }

        //退单编号
        if ($return_id = $this->request->get('return_id')) {
            $query->where('return_id', 'like', "%$return_id%");
        }

        //用户ID
        if ($buyer_user_id = $this->request->get('buyer_user_id')) {
            $query->where('buyer_user_id', '=', $buyer_user_id);
        }

        //退款渠道
        if ($return_channel_code = $this->request->get('return_channel_code')) {
            $query->where('return_channel_code', '=', $return_channel_code);
        }

        //时间筛选
        if ($return_add_start = $this->request->get('return_add_start')) {
            $query->where('return_add_time', '>=', $return_add_start);
        }

        //时间筛选
        if ($return_add_end = $this->request->get('return_add_end')) {
            $query->where('return_add_time', '<=', $return_add_end);
        }

    }


    protected function after($model)
    {
        return $model->orderBy('return_add_time', 'DESC');
    }

}
