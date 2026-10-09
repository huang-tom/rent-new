<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class LogErrorCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //日志名称
        if ($log_error_name = $this->request->get('log_error_name')) {
            $query->where('log_error_name', 'like', "%$log_error_name%");
        }

        if ($log_error_type = $this->request->get('log_error_type')) {
            $query->where('log_error_type', '=', $log_error_type);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('log_error_id', 'DESC');
    }

}
