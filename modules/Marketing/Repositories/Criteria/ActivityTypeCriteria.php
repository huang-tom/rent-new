<?php

namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityTypeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动ID
        if ($activity_type_id = $this->request->get('activity_type_id')) {
            $query->where('activity_type_id', '=', $activity_type_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('activity_type_id', 'ASC');
    }
}
