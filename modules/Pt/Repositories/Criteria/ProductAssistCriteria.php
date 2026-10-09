<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductAssistCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($assist_name = $this->request->get('assist_name')) {
            $query->where('assist_name', 'like', '%' . $assist_name . '%');
        }

        //类型ID
        if ($type_id = $this->request->get('type_id')) {
            $query->where('type_id', '=', $type_id);
        }

        //是否支持筛选
        if ($assist_is_search = $this->request->get('assist_is_search')) {
            $query->where('assist_is_search', '=', $assist_is_search);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('assist_sort', 'ASC')->orderBy('assist_id', 'DESC');
    }

}
