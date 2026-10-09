<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConfigTypeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($config_type_name = $this->request->get('config_type_name')) {
            $query->where('config_type_name', 'like', "%$config_type_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('config_type_sort', 'ASC')->orderBy('config_type_id', 'ASC');
    }

}
