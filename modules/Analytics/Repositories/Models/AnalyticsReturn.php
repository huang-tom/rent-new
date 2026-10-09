<?php

namespace Modules\Analytics\Repositories\Models;

use App\Exceptions\ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsReturn.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsReturn extends Model
{

    /**
     * 统计退单数量
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return int
     * @throws ErrorException
     */
    public function getReturnNum($stime, $etime, $return_state_ids = [])
    {
        // 初始化where条件
        $where_sql = "";
        $bindings = [];

        if (!empty($stime) && !empty($etime)) {
            $where_sql .= " AND return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL查询语句
        $sql = "
            SELECT
                COUNT(*) AS num
            FROM
                trade_order_return
            WHERE
                1 {$where_sql}";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            if (!empty($result) && isset($result[0]->num)) {
                $out = $result[0]->num;
            } else {
                $out = 0;
            }

            return $out;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 时间段内退单金额
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return int
     * @throws ErrorException
     */
    public function getReturnAmount($stime, $etime, $return_state_ids = [])
    {
        // 初始化 where 条件
        $where_sql = "";
        $bindings = [];

        if (!empty($stime) && !empty($etime)) {
            $where_sql .= " AND return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL 查询语句
        $sql = "
            SELECT
                SUM(trade_order_return.return_refund_amount) AS num
            FROM
                trade_order_return
            WHERE
                1 {$where_sql}";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            if (!empty($result) && isset($result[0]->num)) {
                $out = $result[0]->num;
            } else {
                $out = 0;
            }

            return $out;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 时间段退款金额
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return array
     * @throws ErrorException
     */
    public function getReturnAmountTimeline($stime, $etime, $return_state_ids = [])
    {
        // 初始化 where 条件
        $where_sql = "";
        $bindings = [];

        if ($stime && $etime) {
            $where_sql .= " AND trade_order_return.return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND trade_order_return.return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL 查询语句
        $sql = "
            SELECT
                FROM_UNIXTIME(trade_order_return.return_add_time / 1000, '%m-%d') AS time,
                SUM(trade_order_return.return_refund_amount) AS num
            FROM
                trade_order_return
            LEFT JOIN
                trade_order_base ON trade_order_return.order_id = trade_order_base.order_id
            WHERE
                1 {$where_sql}
            GROUP BY time
            ORDER BY time";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            return $result;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnTimeLine($stime, $etime, $return_state_ids = [])
    {
        // 构建查询条件
        $query = DB::table('trade_order_return')
            ->select(DB::raw("FROM_UNIXTIME(return_add_time / 1000, '%m-%d') AS time"), DB::raw('COUNT(*) AS num'))
            ->whereRaw('1 = 1');

        if ($stime && $etime) {
            $query->whereBetween('return_add_time', [$stime, $etime]);
        }

        if (!empty($return_state_ids)) {
            $query->whereIn('return_state_id', $return_state_ids);
        }

        // 执行查询并获取结果
        $results = $query->groupBy('time')
            ->orderBy('time')
            ->get();

        return $results;
    }

}
