<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderReturnReasonCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($return_reason_name = $this->request->get('return_reason_name')) {
            $query->where('return_reason_name', 'like', "%$return_reason_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('return_reason_sort', 'ASC')->orderBy('return_reason_id', 'ASC');
    }

}
