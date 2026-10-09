<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserPayCriteria extends Criteria
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
    	return $model->orderBy('user_id','ASC');
    }
}
