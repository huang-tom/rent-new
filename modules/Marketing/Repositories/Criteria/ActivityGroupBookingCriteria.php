<?php

namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityGroupBookingCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动ID
        if ($gb_id = $this->request->get('gb_id')) {
            $query->where('gb_id', '=', $gb_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('gb_id', 'DESC');
    }
}
