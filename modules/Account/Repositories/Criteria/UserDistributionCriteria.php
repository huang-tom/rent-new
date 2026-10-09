<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserDistributionCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //推广员ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //推广员父级ID
        if ($user_parent_id = $this->request->get('user_parent_id')) {
            $query->where('user_parent_id', '=', $user_parent_id);
        }

        //推广员城市合伙人ID
        if ($user_partner_id = $this->request->get('user_partner_id')) {
            $query->where('user_partner_id', '=', $user_partner_id);
        }

        //角色等级
        if ($role_level_id = $this->request->get('role_level_id')) {
            $query->where('role_level_id', '=', $role_level_id);
        }

        //注册时间
        if ($user_time = $this->request->get('user_time')) {
            $query->where('user_time', '>=', $user_time);
        }

        //是否生效
        if ($user_active = $this->request->get('user_active')) {
            $query->where('user_active', '=', $user_active);
        }

        //是否城市合伙人
        if ($user_is_pt = $this->request->get('user_is_pt')) {
            $query->where('user_is_pt', '=', $user_is_pt);
        }

        //是否省代理
        if ($user_is_pa = $this->request->get('user_is_pa')) {
            $query->where('user_is_pa', '=', $user_is_pa);
        }

        //是否区代理
        if ($user_is_da = $this->request->get('user_is_da')) {
            $query->where('user_is_da', '=', $user_is_da);
        }

        //是否市代理
        if ($user_is_ca = $this->request->get('user_is_ca')) {
            $query->where('user_is_ca', '=', $user_is_ca);
        }

        //是否服务商
        if ($user_is_sp = $this->request->get('user_is_sp')) {
            $query->where('user_is_sp', '=', $user_is_sp);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('user_id', 'ASC');
    }
}
