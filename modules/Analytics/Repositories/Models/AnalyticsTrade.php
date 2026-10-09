<?php

namespace Modules\Analytics\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsTrade.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsTrade extends Model
{

    /**
     * @param $start_time
     * @param $end_time
     * @param $trade_is_paid
     * @param $trade_type_id
     * @param $buyer_id
     * @return int|mixed
     */
    public function getTradeAmount($start_time = 0, $end_time = 0, $trade_is_paid = [], $trade_type_id = [], $buyer_id = 0)
    {
        $query = DB::table('pay_consume_trade')
            ->select(DB::raw('SUM(order_payment_amount) as amount'));

        if ($start_time) {
            $query->where('trade_paid_time', '>=', $start_time);
        }
        if ($end_time) {
            $query->where('trade_paid_time', '<=', $end_time);
        }

        if (!empty($trade_is_paid)) {
            $query->whereIn('trade_is_paid', $trade_is_paid);
        }

        if (!empty($trade_type_id)) {
            $query->whereIn('trade_type_id', $trade_type_id);
        }

        if ($buyer_id) {
            $query->where('buyer_id', $buyer_id);
        }

        $result = $query->first();

        return $result && $result->amount ? $result->amount : 0;
    }

}
