<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderInfoCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //订单类型
        if ($kind_id = $this->request->get('kind_id')) {
            $query->where('kind_id', '=', $kind_id);
        }

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //订单状态
        if ($order_state_id = $this->request->get('order_state_id')) {
            if ($order_state_id == 2030) {
                $query->whereIn('order_state_id', [2020, 2030]);
            } else {
                $query->where('order_state_id', '=', $order_state_id);
            }
        }

        //订单标题
        if ($order_title = $this->request->get('order_title')) {
            $query->where('order_title', 'like', "%$order_title%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        // [本地补齐 2026-09-22] 门店ID：门店订单（线下门店维度）筛选依据
        // 背景：trade_order_info 是唯一带 chain_id 的订单表，厂商原实现无此过滤条件，
        //       导致后台无法按门店维度查看订单。仅在参数存在时生效，不影响原有订单列表。
        if ($chain_id = $this->request->get('chain_id')) {
            $query->where('chain_id', '=', $chain_id);
        }

        // [本地补齐 2026-09-22] 所属店铺ID（与 chain_id 区分：store_id 是商家店铺，chain_id 是线下门店）
        if ($store_id = $this->request->get('store_id')) {
            $query->where('store_id', '=', $store_id);
        }

        //时间筛选
        if ($order_stime = $this->request->get('order_stime')) {
            $query->where('create_time', '>=', $order_stime);
        }

        //时间筛选
        if ($order_etime = $this->request->get('order_etime')) {
            $query->where('create_time', '<=', $order_etime);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('create_time', 'DESC');
    }

}
