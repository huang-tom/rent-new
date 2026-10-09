<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserMessageCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //用户昵称
        if ($user_nickname = $this->request->get('user_nickname')) {
            $query->where('user_nickname', 'like', "%$user_nickname%");
        }

        //所属用户
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //消息种类(ENUM):1-发送消息;2-接收消息
        if ($message_kind = $this->request->get('message_kind')) {
            $query->where('message_kind', '=', $message_kind);
        }

        //消息类型(ENUM):1-系统消息;2-用户消息
        if ($message_type = $this->request->get('message_type')) {
            $query->where('message_type', '=', $message_type);
        }

        //消息时间
        if ($start_time = $this->request->get('start_time')) {
            $query->where('message_time', '>=', $start_time);
        }

        if ($this->request->has('message_is_read')) {
            $message_is_read = $this->request->get('message_is_read');
            $query->where('message_is_read', '=', $message_is_read);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('message_id', 'DESC')->orderBy('message_time', 'DESC');
    }
}
