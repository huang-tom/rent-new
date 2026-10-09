<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class DistrictBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //地区名称
        if ($district_name = $this->request->get('district_name')) {
            $query->where('district_name', 'like', "%$district_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('district_sort', 'ASC')->orderBy('district_id', 'ASC');
    }

}
