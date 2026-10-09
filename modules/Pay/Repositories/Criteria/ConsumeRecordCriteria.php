<?php

namespace Modules\Pay\Repositories\Criteria;

use App\Support\StateCode;
use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConsumeRecordCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //user_nickname
        if ($user_nickname = $this->request->get('user_nickname')) {
            $query->where('user_nickname', 'like', "%$user_nickname%");
        }

        //流水类型 收入/支出
        $change_type = $this->request->input('change_type', 0);
        if ($change_type == 1) {
            $query->whereIn('trade_type_id', [
                StateCode::TRADE_TYPE_SHOPPING,
                StateCode::TRADE_TYPE_TRANSFER,
                StateCode::TRADE_TYPE_WITHDRAW,
                StateCode::TRADE_TYPE_REFUND_PAY,
                StateCode::TRADE_TYPE_COMMISSION_TRANSFER
            ]);
        }
        if ($change_type == 2) {
            $query->whereIn('trade_type_id', [
                StateCode::TRADE_TYPE_DEPOSIT,
                StateCode::TRADE_TYPE_SALES,
                StateCode::TRADE_TYPE_COMMISSION,
                StateCode::TRADE_TYPE_REFUND_GATHERING,
                StateCode::TRADE_TYPE_TRANSFER_GATHERING
            ]);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('record_time', 'DESC');
    }
}
