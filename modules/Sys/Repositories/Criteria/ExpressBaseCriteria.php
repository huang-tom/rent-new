<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ExpressBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //快递名称
        if ($express_name = $this->request->get('express_name')) {
            $query->where('express_name', 'like', "%$express_name%");
        }

        //是否启用
        if ($express_enable = $this->request->get('express_enable')) {
            $query->where('express_enable', '=', $express_enable);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('express_order', 'ASC')->orderBy('express_id', 'ASC');
    }

}
