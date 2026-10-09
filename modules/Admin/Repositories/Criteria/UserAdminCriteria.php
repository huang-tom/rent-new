<?php

namespace Modules\Admin\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserAdminCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        if ($user_role_id = $this->request->get('user_role_id')) {
            $query->where('user_role_id', '=', $user_role_id);
        }

        if ($user_is_superadmin = $this->request->get('user_is_superadmin')) {
            $query->where('user_is_superadmin', '=', $user_is_superadmin);
        }

        if ($role_id = $this->request->get('role_id')) {
            $query->where('role_id', '=', $role_id);
        }

        if ($store_id = $this->request->get('store_id')) {
            $query->where('store_id', '=', $store_id);
        }

        if ($chain_id = $this->request->get('chain_id')) {
            $query->where('chain_id', '=', $chain_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('user_admin_ctime', 'ASC');
    }
}
