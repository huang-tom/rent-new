<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductBrandCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //品牌名称
        if ($brand_name = $this->request->get('brand_name')) {
            $query->where('brand_name', 'like', '%' . $brand_name . '%');
        }

        //品牌ID
        if ($brand_id = $this->request->get('brand_id')) {
            $query->where('brand_id', '=', $brand_id);
        }

        //分类ID
        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', '=', $category_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('brand_sort', 'ASC')->orderBy('brand_id', 'DESC');
    }

}
