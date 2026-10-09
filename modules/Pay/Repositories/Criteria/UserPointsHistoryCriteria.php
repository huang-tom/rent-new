<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserPointsHistoryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

    }

    protected function after($model)
    {
    	return $model->orderBy('points_log_time','DESC')->orderBy('points_log_date','DESC');
    }
}
