<?php

namespace Modules\Analytics\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsProduct.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsProduct extends Model
{

    //获取产品时间线数据
    public function getProductTimeLine($stime, $etime)
    {
        $query = DB::table('pt_product_index')
            ->select(DB::raw('COUNT(*) as num'), DB::raw("FROM_UNIXTIME(ROUND(product_add_time / 1000), '%m-%d') AS time"))
            ->whereRaw('1 = 1');

        if ($stime && $etime) {
            $query->whereBetween('product_add_time', [$stime, $etime]);
        }

        $results = $query->groupBy('time')
            ->orderBy('time')
            ->get();

        return $results;
    }


    /*
     * 获取产品数量
     * @param $stime
     * @param $etime
     * @param $product_state_id
     * @param $category_id
     */
    public function getProductNum($stime, $etime, $product_state_id = 0, $category_id = 0)
    {
        $query = DB::table('pt_product_index')
            ->select(DB::raw('COUNT(*) as num'))
            ->whereRaw('1 = 1'); // 保证基本条件存在

        if ($stime && $etime) {
            $query->whereBetween('product_add_time', [$stime, $etime]);
        }

        if ($product_state_id) {
            $query->where('product_state_id', $product_state_id);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $result = $query->first();
        $num = $result->num ?? 0;

        return $num;
    }

}
