<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class CrontabBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($crontab_name = $this->request->get('crontab_name')) {
            $query->where('crontab_name', 'like', "%$crontab_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('crontab_id', 'ASC');
    }

}
