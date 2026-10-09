<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConsumeDepositCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        if ($deposit_no = $this->request->get('deposit_no')) {
            $query->where('deposit_no', '=', $deposit_no);
        }

        //交易号
        if ($deposit_trade_no = $this->request->get('deposit_trade_no')) {
            $query->where('deposit_trade_no', '=', $deposit_trade_no);
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('deposit_id', 'ASC');
    }
}
