<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class DictBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //字典名称
        if ($dict_name = $this->request->get('dict_name')) {
            $query->where('dict_name', 'like', "%$dict_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('dict_sort', 'ASC')->orderBy('dict_id', 'ASC');
    }

}
