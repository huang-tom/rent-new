<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserCartCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //activity_id
        if ($activity_id = $this->request->get('activity_id')) {
            $query->where('activity_id', '=', $activity_id);
        }

        //cart_select
        if ($cart_select = $this->request->get('cart_select')) {
            $query->where('cart_select', '=', $cart_select);
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('cart_id', 'DESC');
    }

}
