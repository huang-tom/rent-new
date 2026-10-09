<?php

namespace Modules\Pay\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use Modules\Pay\Repositories\Models\ConsumeTrade;

/**
 * Class ConsumeTradeRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class ConsumeTradeRepositoryEloquent extends BaseRepository implements ConsumeTradeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConsumeTrade::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /*
     * 创建交易订单
     * @param int $user_id
     * @param array $order_row
     * @param int $trade_type_id
     * @return ConsumeTrade
     */
    public function createConsumeTrade(int $user_id, array $order_row, int $trade_type_id = StateCode::TRADE_TYPE_SHOPPING): ConsumeTrade
    {
        $buyer_store_id = 0;
        $seller_id = 10001;

        $consume_trade = [
            'trade_title' => $order_row['order_title'],
            'order_id' => $order_row['order_id'],
            'buyer_id' => $user_id,
            'buyer_store_id' => $buyer_store_id,
            'store_id' => $order_row['store_id'],
            'subsite_id' => $order_row['subsite_id'],
            'seller_id' => $seller_id,
            'chain_id' => $order_row['chain_id'],
            'trade_is_paid' => StateCode::ORDER_PAID_STATE_NO,
            'trade_type_id' => $trade_type_id,
            'payment_channel_id' => 0,
            'trade_mode_id' => 1,
            'currency_id' => $order_row['currency_id'],
            'currency_symbol_left' => $order_row['currency_symbol_left'],
            'order_payment_amount' => $order_row['order_payment_amount'],
            'order_commission_fee' => $order_row['order_commission_fee'],
            'trade_payment_amount' => $order_row['order_payment_amount'],
            'trade_payment_money' => 0,
            'trade_payment_recharge_card' => 0,
            'trade_payment_points' => 0,
            'trade_payment_sp' => 0,
            'trade_payment_credit' => 0,
            'trade_payment_redpack' => 0,
            'trade_discount' => $order_row['order_discount_amount'],
            'trade_amount' => $order_row['order_item_amount'],
            'trade_create_time' => getTime()
        ];

        return $this->add($consume_trade);
    }
}
