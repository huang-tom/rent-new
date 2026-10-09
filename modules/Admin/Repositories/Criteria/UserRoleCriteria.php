<?php

namespace Modules\Admin\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserRoleCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //角色名称
        if ($user_role_name = $this->request->get('user_role_name')) {
            $query->where('user_role_name', 'like', "%$user_role_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('user_role_id', 'ASC');
    }
}
