<?php

namespace Modules\Analytics\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsOrder.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsSys extends Model
{

    /**
     * @param $stime
     * @param $etime
     * @return false|int
     */
    public function getVisitor($stime, $etime)
    {
        $whereSet = "";

        if (!empty($stime) && !empty($etime)) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS num
            FROM sys_access_history
            WHERE 1 %s",
            $whereSet
        );

        try {
            $result = DB::selectOne($sql);

            if ($result && isset($result->num)) {
                return $result->num;
            } else {
                return 0;
            }
        } catch (QueryException $e) {
            // 记录日志或处理错误
            return false;
        }
    }

    public function getAccessNum($stime, $etime)
    {
        $whereSet = "";

        if (!empty($stime) && !empty($etime)) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num
             FROM sys_access_history
             WHERE 1 %s", $whereSet
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }


    /**
     * 获取访问数量
     * @param $stime
     * @param $etime
     * @return int
     */
    public function getVisitorNum($stime, $etime)
    {
        $where_sql = "";
        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        $result = DB::selectOne($sql);
        return $result ? $result->num : 0;
    }

    public function getAccessItemTimeLine($stime, $etime, $item_id)
    {
        $whereSet = "";

        if ($stime && $etime) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $whereSet .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $whereSet
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemNum($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemUserTimeLine($stime, $etime, $item_id)
    {
        $whereSet = "";

        if ($stime && $etime) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $whereSet .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $whereSet
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemUserNum($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessVisitorTimeLine($stime, $etime)
    {
        $where_sql = "";
        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $where_sql
        );

        try {
            $result = DB::select($sql);
            return $result;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function listAccessItem($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND ash.access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT ash.item_id, ppi.item_name, ppi.product_id, ppi.item_unit_price, ppb.product_name,
                    COUNT(*) AS num
             FROM sys_access_history ash
             INNER JOIN pt_product_item ppi ON ash.item_id = ppi.item_id
             INNER JOIN pt_product_base ppb ON ppi.product_id = ppb.product_id
             WHERE 1 %s
             GROUP BY ash.item_id,ppi.item_name,ppi.product_id,ppi.item_unit_price,ppb.product_name
             ORDER BY num DESC
             LIMIT 0, 100", $where_sql
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

}
