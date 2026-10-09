<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserFriendCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }

    protected function after($model)
    {
    	return $model->orderBy('user_friend_addtime', 'ASC');
    }
}
