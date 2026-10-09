<?php

namespace Modules\Shop\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class StoreExpressLogisticsCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //物流名称
        if ($logistics_name = $this->request->get('logistics_name'))
        {
            $query->where('logistics_name', 'like', "%$logistics_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('logistics_id', 'DESC');
    }

}
