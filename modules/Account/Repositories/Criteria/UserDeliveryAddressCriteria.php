<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserDeliveryAddressCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //收货人名称
        if ($ud_name = $this->request->get('ud_name')) {
            $query->where('ud_name', 'like', "%$ud_name%");
        }

        //手机号
        if ($ud_mobile = $this->request->get('ud_mobile')) {
            $query->where('ud_mobile', 'like', "%$ud_mobile%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('ud_is_default', 'DESC')->orderBy('ud_id', 'DESC');
    }
}
