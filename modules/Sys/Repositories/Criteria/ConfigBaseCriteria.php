<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConfigBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //类型ID
        if ($config_type_id = $this->request->get('config_type_id')) {
            $query->where('config_type_id', '=', $config_type_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('config_sort', 'ASC')->orderBy('config_key', 'ASC');
    }

}
