<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConsumeTradeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //交易类型
        if ($trade_type_id = $this->request->get('trade_type_id')) {
            $query->where('trade_type_id', '=', $trade_type_id);
        }

        //付款状态
        if ($trade_is_paid = $this->request->get('trade_is_paid')) {
            $query->where('trade_is_paid', '=', $trade_is_paid);
        }

        //标题
        if ($trade_title = $this->request->get('trade_title')) {
            $query->where('trade_title', 'like', "%$trade_title%");
        }

        //用户ID
        if ($buyer_id = $this->request->get('buyer_id')) {
            $query->where('buyer_id', '=', $buyer_id);
        }

        //时间筛选
        if ($order_stime = $this->request->get('order_stime')) {
            $query->where('trade_create_time', '>=', $order_stime);
        }

        //时间筛选
        if ($order_etime = $this->request->get('order_etime')) {
            $query->where('trade_create_time', '<=', $order_etime);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('trade_create_time', 'DESC');
    }
}
