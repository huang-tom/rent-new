<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\DistributionOrderRepository;
use Modules\Trade\Repositories\Models\DistributionOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class DistributionOrderRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class DistributionOrderRepositoryEloquent extends BaseRepository implements DistributionOrderRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return DistributionOrder::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function calCommissionByTime($user_id, $level = 0, $time = null, $uo_active = 1)
    {
        $sql = "
            SELECT
                SUM(uo_buy_commission) uo_buy_commission
            FROM
                `trade_distribution_order`
            WHERE 1 ";

        // [安全修复] 原为 "= {$user_id}" 字符串拼接，改为 %d 强制转整数，避免注入
        $sql = $sql . sprintf(" AND  user_id  = %d ", $user_id);

        if ($level)
        {
            $sql = $sql . sprintf(" AND  uo_level  = %d ", $level);
        }

        if (true)
        {
            $sql = $sql . " AND  uo_is_paid  = 1 ";
        }

        if ($time)
        {
            $sql = $sql . " AND uo_time >=  " . $time;
        }

        if ($uo_active)
        {
            $sql = $sql . " AND uo_active =  " . $uo_active;
        }

        $sql = $sql . " GROUP BY user_id ";

        $data = DB::select($sql);

        return $data;
    }

}
