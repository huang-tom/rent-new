<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class DistributionOrderCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //买家ID
        if ($buyer_user_id = $this->request->get('buyer_user_id')) {
            $query->where('buyer_user_id', '=', $buyer_user_id);
        }

        //收益归属人id
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //是否生效
        if ($uo_active = $this->request->get('uo_active')) {
            $query->where('uo_active', '=', $uo_active);
        }

        //是否支付
        if ($uo_is_paid = $this->request->get('uo_is_paid')) {
            $query->where('uo_is_paid', '=', $uo_is_paid);
        }

        //创建时间
        if ($uo_time_start = $this->request->get('uo_time_start')) {
            $query->where('uo_time', '>=', $uo_time_start);
        }
        if ($uo_time_end = $this->request->get('uo_time_end')) {
            $query->where('uo_time', '<=', $uo_time_end);
        }

        //支付时间
        if ($uo_paytime = $this->request->get('uo_paytime')) {
            $query->where('uo_paytime', '>=', $uo_paytime);
        }

        //收货时间
        if ($uo_receivetime = $this->request->get('uo_receivetime')) {
            $query->where('uo_receivetime', '>=', $uo_receivetime);
        }

        //佣金等级
        if ($uo_level = $this->request->get('uo_level')) {
            $query->where('uo_level', '=', $uo_level);
        }

        //佣金等级s
        if ($uo_levels = $this->request->get('uo_levels')) {
            $query->where('uo_level', 'IN', $uo_levels);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('uo_time', 'DESC');
    }
}
