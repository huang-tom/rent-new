<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserBindConnectCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        if ($bind_nickname = $this->request->get('bind_nickname')) {
            $query->where('bind_nickname', 'like', "%$bind_nickname%");
        }

        //所属用户
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        if ($bind_id = $this->request->get('bind_id')) {
            $query->where('bind_id', '=', $bind_id);
        }

        if ($bind_type = $this->request->get('bind_type')) {
            $query->where('bind_type', '=', $bind_type);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('bind_time', 'DESC');
    }
}
