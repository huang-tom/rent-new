<?php

namespace Modules\O2o\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ChainUserCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        if ($store_id = $this->request->get('store_id')) {
            $query->where('store_id', '=', $store_id);
        }

        if ($chain_id = $this->request->get('chain_id')) {
            $query->where('chain_id', '=', $chain_id);
        }

        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        if ($rights_group_id = $this->request->get('rights_group_id')) {
            $query->where('rights_group_id', '=', $rights_group_id);
        }

        if ($chain_user_is_admin = $this->request->get('chain_user_is_admin')) {
            $query->where('chain_user_is_admin', '=', $chain_user_is_admin);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('chain_user_id', 'ASC');
    }

}
