<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class PageMobileEntranceCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($entrance_name = $this->request->get('entrance_name')) {
            $query->where('entrance_name', 'like', "%$entrance_name%");
        }

    }


    protected function after($model)
    {
        return $model->orderBy('entrance_order', 'ASC')->orderBy('entrance_id', 'ASC');
    }

}
