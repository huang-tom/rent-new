<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class LogActionCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //路径
        if ($log_url = $this->request->get('log_url')) {
            $query->where('log_url', 'like', "%$log_url%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('log_id', 'DESC');
    }

}
