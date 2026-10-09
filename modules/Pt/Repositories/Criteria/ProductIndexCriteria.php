<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductIndexCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //商品名称
        if ($product_name = $this->request->get('product_name')) {
            $query->where('product_name', 'like', '%' . $product_name . '%');
        }

        //商品名称
        if ($product_name = $this->request->get('keywords')) {
            $query->where('product_name', 'like', '%' . $product_name . '%');
        }

        //商品ID
        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

        //审核状态
        if ($product_verify_id = $this->request->get('product_verify_id')) {
            $query->where('product_verify_id', '=', $product_verify_id);
        }

        //销售状态
        if ($product_state_id = $this->request->get('product_state_id')) {
            $query->where('product_state_id', '=', $product_state_id);
        }

        //分类ID
        $category_id = $this->request->get('category_id');
        if ($category_id) {
            if (is_array($category_id)) {
                $query->whereIn('category_id', $category_id);
            } else {
                $query->where('category_id', '=', $category_id);
            }
        }

        //品牌ID
        if ($brand_id = $this->request->get('brand_id')) {
            $query->where('brand_id', '=', $brand_id);
        }

        //商品$product_ids
        if ($product_ids = $this->request->get('product_ids')) {
            $query->whereIn('product_id', $product_ids);
        }

        //最低价格
        if ($product_unit_price_min = $this->request->get('product_unit_price_min')) {
            $query->where('product_unit_price_min', '>=', $product_unit_price_min);
        }

        //价格区间
        if ($product_unit_price_max = $this->request->get('product_unit_price_max')) {
            $query->where('product_unit_price_max', '<=', $product_unit_price_max);
        }

        //商品属性筛选
        if ($this->request->has('product_assist_data')) {
            if ($product_assist_data = $this->request->get('product_assist_data')) {
                $product_assist_data = explode(',', $product_assist_data);
                foreach ($product_assist_data as $assist_item_id) {
                    $query->whereRaw('FIND_IN_SET(?, product_assist_data)', [$assist_item_id]);
                }
            }
        }

        //活动类型筛选
        if ($activity_type_ids = $this->request->get('activity_type_ids')) {
            $activity_type_ids = explode(',', $activity_type_ids);
            foreach ($activity_type_ids as $activity_type_id) {
                $query->whereRaw('FIND_IN_SET(?, activity_type_ids)', [$activity_type_id]);
            }
        }

        //商品标签筛选
        if ($product_tags = $this->request->get('product_tags')) {
            $product_tag_ids = explode(',', $product_tags);
            foreach ($product_tag_ids as $product_tag_id) {
                $query->whereRaw('FIND_IN_SET(?, product_tags)', [$product_tag_id]);
            }
        }

    }

    protected function after($model)
    {
        return $model->orderBy('product_order', 'ASC')->orderBy('product_id', 'DESC');
    }

}
