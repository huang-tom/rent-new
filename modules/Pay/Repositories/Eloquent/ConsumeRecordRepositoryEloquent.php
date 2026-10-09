<?php

namespace Modules\Pay\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Models\ConsumeRecord;

/**
 * Class ConsumeRecordRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class ConsumeRecordRepositoryEloquent extends BaseRepository implements ConsumeRecordRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConsumeRecord::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /*
     * 增加流水
     * @param $trade
     * @param $deposit
     * @param $trade_type_id
     * @param $payment_met_id
     * @return bool
     */
    public function addConsumeRecord($user_id = null, $trade = [], $deposit = [], $trade_type_id = StateCode::TRADE_TYPE_DEPOSIT, $payment_met_id = StateCode::PAYMENT_MET_MONEY)
    {
        $user_nickname = '';
        $store_id = 0;
        $chain_id = 0;
        if (isset($trade['user_nickname'])) {
            $user_nickname = $trade['user_nickname'];
        }
        if (isset($trade['store_id'])) {
            $store_id = $trade['store_id'];
        }
        if (isset($trade['chain_id'])) {
            $chain_id = $trade['chain_id'];
        }

        $record_row = [
            'user_id' => $user_id, //用户ID
            'order_id' => $trade['order_id'], //订单编号
            'user_nickname' => $user_nickname,
            'store_id' => $store_id,
            'chain_id' => $chain_id,

            'payment_channel_id' => $deposit['payment_channel_id'],
            'trade_type_id' => $trade_type_id,  //交易类型
            'payment_met_id' => $payment_met_id, //平台资产类型：余额积分等

            'record_date' => date("Y-m-d H:i:s"),
            'record_year' => date("Y"),
            'record_month' => date("n"),
            'record_day' => date("j"),
            'record_time' => round(microtime(true) * 1000),
        ];

        //支付方式(ENUM)
        if (isset($deposit['payment_type_id'])) {
            $record_row['payment_type_id'] = $deposit['payment_type_id'];
        }
        if (isset($deposit['deposit_payment_type'])) {
            $record_row['payment_type_id'] = $deposit['deposit_payment_type'];
        }

        //标题、描述、总额
        if ($trade_type_id == StateCode::TRADE_TYPE_DEPOSIT) {
            $record_row['record_title'] = $deposit['deposit_subject'];
            $record_row['record_desc'] = $deposit['deposit_body'];
            $record_row['record_total'] = $deposit['deposit_total_fee'];
        } else {
            $record_row['record_title'] = $trade['trade_title'];
            $record_row['record_desc'] = $trade['trade_desc'];
            $record_row['record_total'] = $trade['record_total'];
        }

        if (isset($trade['record_money'])) {
            $record_row['record_money'] = $trade['record_money'];
        } else {
            $record_row['record_money'] = $record_row['record_total'];
        }

        //佣金
        if (isset($trade['record_commission_fee'])) {
            $record_row['record_commission_fee'] = $trade['record_commission_fee'];
        }

        return $this->add($record_row);
    }

}
