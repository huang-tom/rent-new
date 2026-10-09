<?php

namespace Modules\Shop\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserVoucherCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //优惠券状态
        if ($voucher_state_id = $this->request->get('voucher_state_id')) {
            $query->where('voucher_state_id', '=', $voucher_state_id);
        }

        //活动ID
        if ($activity_id = $this->request->get('activity_id')) {
            $query->where('activity_id', '=', $activity_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('user_voucher_time', 'DESC');
    }

}
