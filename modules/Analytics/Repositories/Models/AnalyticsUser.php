<?php

namespace Modules\Analytics\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsOrder.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsUser extends Model
{

    /**
     * 统计新增用户数量
     * @param $start_time
     * @param $end_time
     * @return int|mixed
     */
    public function getRegUserNum($start_time, $end_time)
    {
        $query = DB::table('account_user_login')
            ->select(DB::raw('COUNT(*) AS num'));
        if ($start_time) {
            $query->where('user_reg_time', '>=', $start_time);
        }
        if ($end_time) {
            $query->where('user_reg_time', '<=', $end_time);
        }

        $result = $query->first();

        return $result ? $result->num : 0;
    }


    /**
     * 获取用户时间线数据
     *
     * @param int $start_time 开始时间
     * @param int $end_time 结束时间
     * @return array 用户时间线数据
     */
    public function getUserTimeLine($start_time, $end_time): array
    {
        // 构建查询条件
        $where_sql = '';
        if ($start_time && $end_time) {
            $where_sql = sprintf(" AND user_reg_time BETWEEN %d AND %d", $start_time, $end_time);
        }

        // 构建 SQL 查询
        $sql = sprintf("
            SELECT FROM_UNIXTIME(ROUND(user_reg_time / 1000), '%%m-%%d') AS time,
                   COUNT(*) AS num
            FROM account_user_login
            WHERE 1 %s
            GROUP BY time
            ORDER BY time
        ", $where_sql);

        // 执行查询并获取结果
        $result = DB::select($sql);
        return $result;
    }

}
