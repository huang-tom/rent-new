<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ConsumeWithdrawCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //提现用户
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //提现状态(ENUM):0-申请中;1-提现通过;2-驳回;3-打款完成
        $withdraw_state = $this->request->get('withdraw_state');

        if (!is_null($withdraw_state)) {
            $query->where('withdraw_state', '=', $withdraw_state);
        }

        //提现方式(ENUM):0-余额提现;1-佣金提现
        if ($withdraw_mode = $this->request->get('withdraw_mode')) {
            $query->where('withdraw_mode', '=', $withdraw_mode);
        }

        //创建时间
        if ($withdraw_time_start = $this->request->get('withdraw_time_start')) {
            $query->where('withdraw_time', '>=', $withdraw_time_start);
        }
        if ($withdraw_time_end = $this->request->get('withdraw_time_end')) {
            $query->where('withdraw_time', '<=', $withdraw_time_end);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('withdraw_id', 'DESC');
    }
}
