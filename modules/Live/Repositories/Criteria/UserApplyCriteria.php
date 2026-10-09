<?php

namespace Modules\Live\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserApplyCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($apply_state = $this->request->get('apply_state')) {
            $query->where('apply_state', '=', $apply_state);
        }
    }

    protected function after($model)
    {
    	return $model->orderBy('apply_time', 'DESC');
    }
}
