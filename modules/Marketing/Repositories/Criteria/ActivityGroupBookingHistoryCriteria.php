<?php

namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityGroupBookingHistoryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动ID
        if ($gbh_id = $this->request->get('gbh_id')) {
            $query->where('gbh_id', '=', $gbh_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('gbh_id', 'DESC');
    }
}
