<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserLevelCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($user_level_name = $this->request->get('user_level_name')) {
            $query->where('user_level_name', 'like', "%$user_level_name%");
        }

        if ($user_level_id = $this->request->get('user_level_id')) {
            $query->where('user_level_id', '=', $user_level_id);
        }
    }

    protected function after($model)
    {
    	return $model->orderBy('user_level_id','ASC')->orderBy('user_level_exp','ASC');
    }
}
