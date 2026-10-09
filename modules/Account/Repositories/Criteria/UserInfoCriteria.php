<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserInfoCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //账号
        if ($user_account = $this->request->get('user_account')) {
            $query->where('user_account', 'like', "%$user_account%");
        }

        //昵称
        if ($user_nickname = $this->request->get('user_nickname')) {
            $query->where('user_nickname', 'like', "%$user_nickname%");
        }

        //手机号
        if ($user_mobile = $this->request->get('user_mobile')) {
            $query->where('user_mobile', 'like', "%$user_mobile%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //user_level_id
        if ($user_level_id = $this->request->get('user_level_id')) {
            $query->where('user_level_id', '=', $user_level_id);
        }

        if ($user_is_authentication = $this->request->get('user_is_authentication')) {
            $query->where('user_is_authentication', '=', $user_is_authentication);
        }

        if ($tag_ids = $this->request->get('tag_ids')) {
            $query->whereRaw('FIND_IN_SET(?, tag_ids)', [$tag_ids]);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('user_id', 'DESC');
    }
}
