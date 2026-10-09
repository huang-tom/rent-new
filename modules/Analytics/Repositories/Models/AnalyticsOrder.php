<?php

namespace Modules\Analytics\Repositories\Models;

use App\Exceptions\ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Class AnalyticsOrder.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsOrder extends Model
{

    /**
     * @param $start_time
     * @param $end_time
     * @param $order_state_ids
     * @param $order_is_paids
     * @param $user_id
     * @param $kind_id
     * @return int|mixed
     */
    public function getOrderNum($start_time, $end_time, $order_state_ids = [], $order_is_paids = [], $user_id = 0, $kind_id = 0)
    {
        $query = DB::table('trade_order_info')
            ->select(DB::raw('COUNT(*) AS orderNum'));

        if ($start_time) {
            $query->where('create_time', '>=', $start_time);
        }
        if ($end_time) {
            $query->where('create_time', '<=', $end_time);
        }

        if (!empty($order_state_ids)) {
            $query->whereIn('order_state_id', $order_state_ids);
        }

        if (!empty($order_is_paids)) {
            $query->whereIn('order_is_paid', $order_is_paids);
        }

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        if ($kind_id) {
            $query->where('kind_id', $kind_id);
        }

        $result = $query->first();

        return $result ? $result->orderNum : 0;
    }


    /**
     * 获取支付时间线数据
     *
     * @param int $stime 开始时间
     * @param int $etime 结束时间
     * @return array
     */
    public function getPayTimeLine($stime, $etime)
    {
        $where_sql = "";
        if ($stime && $etime) {
            // [安全修复] 原为 " BETWEEN {$stime} AND {$etime}" 字符串拼接，存在未授权 SQL 注入。
            // 改用 %d 强制转为整数，与本项目 AnalyticsSys.php 既有写法保持一致。
            $where_sql = sprintf(" AND trade_paid_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = "
            SELECT
                FROM_UNIXTIME(trade_paid_time / 1000, '%m-%d') AS time,
                SUM(order_payment_amount) AS num
            FROM pay_consume_trade
            WHERE 1 {$where_sql}
            GROUP BY time
            ORDER BY time";

        // 执行查询并获取结果
        $result = DB::select(DB::raw($sql));

        return $result;
    }


    /**
     * getSaleOrderAmount
     * @param $stime
     * @param $etime
     * @return array
     */
    public function getSaleOrderAmount($stime, $etime)
    {
        // 初始化where条件
        $where_sql = "";
        $bindings = [];

        if ($stime && $etime) {
            $where_sql .= " AND trade_order_info.create_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        // SQL查询语句
        $sql = "
            SELECT
                FROM_UNIXTIME(trade_order_info.create_time / 1000, '%m-%d') AS time,
                SUM(trade_order_base.order_payment_amount) AS num
            FROM
                trade_order_info
            LEFT JOIN
                trade_order_base ON trade_order_info.order_id = trade_order_base.order_id
            WHERE
                1 {$where_sql}
            AND
                trade_order_info.order_is_paid IN (3012, 3013)
            GROUP BY time
            ORDER BY time";

        // 执行查询  返回查询结果
        $result = DB::select($sql, $bindings);

        return $result ?? [];
    }


    /**
     * @param $stime
     * @param $etime
     * @return array
     */
    public function getOrderCustomerNumTimeline($stime, $etime)
    {
        // 初始化where条件
        $where_sql = "";
        $bindings = [];

        if ($stime && $etime) {
            $where_sql = " AND create_time BETWEEN :stime AND :etime";
            $bindings = ['stime' => $stime, 'etime' => $etime];
        }

        // SQL查询语句
        $sql = "
            SELECT
                FROM_UNIXTIME(create_time / 1000, '%m-%d') AS time,
                COUNT(DISTINCT user_id) AS num
            FROM
                trade_order_info
            WHERE
                1 {$where_sql}
            GROUP BY time
            ORDER BY time";

        // 执行查询
        $result = DB::select($sql, $bindings);

        return $result ?? [];
    }


    /**
     * @param $request
     * @return int
     * @throws ErrorException
     */
    public function getOrderAmount($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $order_state_id = $request->input('order_state_id', []);
        $order_is_paid = $request->input('order_is_paid', []);
        $user_id = $request->input('user_id', 0);
        $kind_id = $request->input('kind_id', 0);

        // 初始化where条件
        $whereSet = "";
        $bindings = [];

        if ($stime && $etime) {
            $whereSet .= " AND trade_order_info.create_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($order_state_id)) {
            $whereSet .= " AND trade_order_info.order_state_id IN (" . implode(',', array_fill(0, count($order_state_id), '?')) . ")";
            $bindings = array_merge($bindings, $order_state_id);
        }

        if ($user_id) {
            $whereSet .= " AND trade_order_info.user_id = :user_id";
            $bindings['user_id'] = $user_id;
        }

        if ($kind_id) {
            $whereSet .= " AND trade_order_info.kind_id = :kind_id";
            $bindings['kind_id'] = $kind_id;
        }

        if (!empty($order_is_paid)) {
            $whereSet .= " AND trade_order_info.order_is_paid IN (" . implode(',', array_fill(0, count($order_is_paid), '?')) . ")";
            $bindings = array_merge($bindings, $order_is_paid);
        }

        // SQL查询语句
        $sql = "
            SELECT
                SUM(trade_order_base.order_payment_amount) AS num
            FROM
                trade_order_info
            LEFT JOIN
                trade_order_base ON trade_order_info.order_id = trade_order_base.order_id
            WHERE
                1 {$whereSet}
            AND
                trade_order_info.order_is_paid IN (3012, 3013)";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回结果
            if (!empty($result) && isset($result[0]->num)) {
                $out = $result[0]->num;
            } else {
                $out = 0;
            }

            return $out;

        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 根据时间段分组订单数目
     * @param $stime
     * @param $etime
     * @return array
     */
    public function getOrderTimeLine($stime, $etime)
    {
        // 初始化where条件
        $where_sql = "";
        $bindings = [];

        if (!empty($stime) && !empty($etime)) {
            $where_sql .= " AND create_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        // SQL查询语句
        $sql = "
            SELECT
                FROM_UNIXTIME(create_time / 1000, '%m-%d') AS time,
                COUNT(*) AS num
            FROM
                trade_order_info
            WHERE
                1 {$where_sql}
            GROUP BY time
            ORDER BY time";

        // 执行查询 返回查询结果
        $result = DB::select($sql, $bindings);

        return $result ?? [];
    }


    /**
     * 获取订单商品数量
     */
    public function getOrderItemNum($request)
    {
        $stime = $request->input('stime');
        $etime = $request->input('etime');
        $store_id = $request->input('store_id');
        $product_id = $request->input('product_id');
        $item_id = $request->input('item_id');
        $category_id = $request->input('category_id');
        $product_name = $request->input('product_name');
        $store_type = $request->input('store_type');
        $kind_id = $request->input('kind_id');

        // 构建查询
        $query = DB::table('trade_order_item as i')
            ->leftJoin('trade_order_info as b', 'i.order_id', '=', 'b.order_id')
            ->select(DB::raw('COUNT(*) AS num'))
            ->whereRaw('1 = 1'); // 保证基础条件

        // 添加时间范围条件
        if ($stime && $etime) {
            $query->whereBetween('b.create_time', [$stime, $etime]);
        }

        // 添加其他查询条件
        if ($store_id) {
            $query->where('i.store_id', $store_id);
        }

        if (!empty($product_id)) {
            $query->where('i.product_id', $product_id);
        }

        if (!empty($item_id)) {
            $query->whereIn('i.item_id', explode(',', $item_id)); // 假设 item_id 是逗号分隔的字符串
        }

        if (!empty($category_id)) {
            $query->whereIn('i.category_id', explode(',', $category_id)); // 假设 category_id 是逗号分隔的字符串
        }

        if (!empty($product_name)) {
            $query->where('i.product_name', 'like', "%$product_name%");
        }

        if (!empty($store_type)) {
            $query->where('b.store_type', $store_type);
        }

        if (!empty($kind_id)) {
            $query->where('b.kind_id', $kind_id);
        }

        // 执行查询并获取结果
        $result = $query->first();
        $num = $result->num ?? 0;

        return $num;
    }


    /**
     * 列出订单商品数量
     */
    public function listOrderItemNum($request)
    {
        $stime = $request->input('stime');
        $etime = $request->input('etime');
        $store_id = $request->input('store_id');
        $product_id = $request->input('product_id');
        $item_id = $request->input('item_id');
        $category_id = $request->input('category_id');
        $product_name = $request->input('product_name');
        $store_type = $request->input('store_type');
        $kind_id = $request->input('kind_id');

        // 构建查询
        $query = DB::table('trade_order_item as i')
            ->leftJoin('trade_order_info as b', 'i.order_id', '=', 'b.order_id')
            ->select(
                'i.product_id',
                'i.item_id',
                'i.order_item_image',
                'i.product_name',
                'i.item_name',
                DB::raw('SUM(i.order_item_quantity) AS num'),
                DB::raw('SUM(i.order_item_amount) AS order_item_amount_sum')
            )
            ->whereRaw('1 = 1'); // 保证基础条件

        // 添加时间范围条件
        if ($stime && $etime) {
            $query->whereBetween('b.create_time', [$stime, $etime]);
        }

        // 添加其他查询条件
        if ($store_id) {
            $query->where('i.store_id', $store_id);
        }

        if ($product_id) {
            $query->where('i.product_id', $product_id);
        }

        if ($item_id) {
            $query->whereIn('i.item_id', explode(',', $item_id)); // 假设 item_id 是逗号分隔的字符串
        }

        if ($category_id) {
            $query->whereIn('i.category_id', explode(',', $category_id)); // 假设 category_id 是逗号分隔的字符串
        }

        if ($product_name) {
            $query->where('i.product_name', 'like', "%$product_name%");
        }

        if ($store_type) {
            $query->where('b.store_type', $store_type);
        }

        if ($kind_id) {
            $query->where('b.kind_id', $kind_id);
        }

        // 分组、排序并限制结果
        $results = $query->groupBy('i.product_id',
            'i.item_id',
            'i.order_item_image',
            'i.product_name',
            'i.item_name')
            ->orderBy('num', 'desc')
            ->limit(100)
            ->get();

        return $results ?? [];
    }


    public function getOrderItemNumTimeLine($input)
    {
        $whereSet = '';

        if (!empty($input['stime']) && !empty($input['etime'])) {
            $whereSet .= sprintf(" AND b.create_time BETWEEN %d AND %d", $input['stime'], $input['etime']);
        }

        if (!empty($input['store_id'])) {
            $whereSet .= sprintf(" AND i.store_id = %d", $input['store_id']);
        }

        if (!empty($input['product_id'])) {
            $whereSet .= sprintf(" AND i.product_id = %d", $input['product_id']);
        }

        if (!empty($input['item_id'])) {
            $whereSet .= sprintf(" AND i.item_id IN (%s)", implode(",", $input['item_id']));
        }

        if (!empty($input['category_id'])) {
            $whereSet .= sprintf(" AND i.category_id IN (%s)", implode(",", $input['category_id']));
        }

        if (!empty($input['product_name'])) {
            $whereSet .= sprintf(" AND i.product_name LIKE '%%%s%%'", $input['product_name']);
        }

        if (!empty($input['store_type'])) {
            $whereSet .= sprintf(" AND b.store_type = %d", $input['store_type']);
        }

        if (!empty($input['kind_id'])) {
            $whereSet .= sprintf(" AND b.kind_id = %d", $input['kind_id']);
        }

        $sql = sprintf("
            SELECT
                FROM_UNIXTIME(b.create_time / 1000, '%%m-%%d') AS time,
                COUNT(*) AS num
            FROM trade_order_item i
            LEFT JOIN trade_order_info b ON i.order_id = b.order_id
            WHERE 1 %s
            GROUP BY time
            ORDER BY time
        ", $whereSet);

        return DB::select($sql);
    }

}
