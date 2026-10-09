<?php

namespace Modules\Analytics\Services;

use App\Support\StateCode;
use Modules\Analytics\Repositories\Models\AnalyticsTrade;

/**
 * Class AnalyticsTradeService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsTradeService
{
    private $analyticsTrade;

    public function __construct(AnalyticsTrade $analyticsTrade)
    {
        $this->analyticsTrade = $analyticsTrade;
    }


    /**
     * 销售额
     * @return array
     */
    public function getSalesAmount()
    {
        $today = getToday();
        $trade_is_paid = [StateCode::ORDER_PAID_STATE_PART, StateCode::ORDER_PAID_STATE_YES];
        $trade_type_id = [StateCode::TRADE_TYPE_SHOPPING, StateCode::TRADE_TYPE_FAVORABLE];

        $data['today'] = $this->analyticsTrade->getTradeAmount($today['start'], $today['end'], $trade_is_paid, $trade_type_id);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsTrade->getTradeAmount($yesterday['start'], $yesterday['end'], $trade_is_paid, $trade_type_id);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsTrade->getTradeAmount($month['start'], $month['end'], $trade_is_paid, $trade_type_id);;

        return $data;
    }

}
