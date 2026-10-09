<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class DictItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //字典项名称
        if ($dict_item_name = $this->request->get('dict_item_name')) {
            $query->where('dict_item_name', 'like', "%$dict_item_name%");
        }

        //字典类型
        if ($dict_id = $this->request->get('dict_id')) {
            $query->where('dict_id', '=', $dict_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('dict_item_sort', 'ASC')->orderBy('dict_item_id', 'ASC');
    }

}
