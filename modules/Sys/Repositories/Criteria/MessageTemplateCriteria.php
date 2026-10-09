<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class MessageTemplateCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($message_name = $this->request->get('message_name')) {
            $query->where('message_name', 'like', "%$message_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('message_order', 'ASC')->orderBy('message_id', 'ASC');
    }

}
