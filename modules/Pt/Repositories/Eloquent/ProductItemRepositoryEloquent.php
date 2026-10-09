<?php

namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Models\ProductItem;

/**
 * Class ProductItemRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductItemRepositoryEloquent extends BaseRepository implements ProductItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    //根据主键获取数据
    public function getOne($id)
    {
        $res = $this->model->find($id);
        if ($res === NULL) {
            $res = [];
        } else {
            $res = $res->toArray();
            $res['available_quantity'] = $res['item_quantity'] - $res['item_quantity_frozen'];
        }

        $this->resetModel();

        return $res;
    }


    /**
     * 根据主键查询 主键作为key下标 返回
     * @param $ids
     * @param $keyBy
     * @return array|mixed[]
     */
    public function gets($ids, $keyBy = true)
    {
        if (empty($ids)) {
            return [];
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $res = $this->findWhereIn('item_id', $ids);
        if ($keyBy) {
            $res = $res->keyBy('item_id');
        }

        $rows = $res ? $res->toArray() : [];
        if (!empty($rows)) {
            foreach ($rows as $k => $row) {
                $rows[$k]['available_quantity'] = $row['item_quantity'] - $row['item_quantity_frozen'];
            }
        }

        return $rows;
    }


    /**
     * 批量更新商品库存
     *
     * @param array $data
     * @return int
     */
    public function batchUpdateQuantity(array $data)
    {
        // 提取所有商品的ID
        $item_ids = array_column($data, 'item_id');
        $case_statements = '';
        $item_ids_str = implode(',', $item_ids);

        // 构建 SQL 的 CASE 语句
        foreach ($data as $row) {
            $case_statements .= "WHEN item_id = {$row['item_id']} THEN {$row['item_quantity']} ";
        }

        // 生成批量更新的 SQL 查询
        $query = "UPDATE pt_product_item SET item_quantity = CASE {$case_statements} END WHERE item_id IN ({$item_ids_str})";

        // 执行更新语句
        return DB::update($query);
    }


}
