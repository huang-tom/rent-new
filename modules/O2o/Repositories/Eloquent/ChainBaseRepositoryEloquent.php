<?php

namespace Modules\O2o\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\O2o\Repositories\Contracts\ChainBaseRepository;
use Modules\O2o\Repositories\Models\ChainBase;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class ChainBaseRepositoryEloquent.
 *
 * @package Modules\O2o\Repositories\Eloquent
 */
class ChainBaseRepositoryEloquent extends BaseRepository implements ChainBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChainBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     *  查询最近的门店列表
     * @param float $latitude 当前纬度
     * @param float $longitude 当前经度
     * @param float $radius 半径（单位：公里）
     * 返回结果为公里
     * @return \Illuminate\Support\Collection
     */
    public function getNearChain(float $latitude, float $longitude, float $radius = 10)
    {
        $haversine = "
            (6378.138 * acos(
                cos(radians(?)) *
                cos(radians(chain_lat)) *
                cos(radians(chain_lng) - radians(?)) +
                sin(radians(?)) *
                sin(radians(chain_lat))
            ))
        ";

        // 子查询：计算距离并生成临时表
        $subQuery = DB::table('o2o_chain_base')
            ->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude]);

        // 外层查询：基于子查询过滤和排序
        return DB::table(DB::raw("({$subQuery->toSql()}) as subquery"))
            ->mergeBindings($subQuery) // 绑定参数
            ->where('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->get();
    }

}
