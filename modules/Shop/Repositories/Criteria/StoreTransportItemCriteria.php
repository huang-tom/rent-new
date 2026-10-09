<?php

namespace Modules\Shop\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class StoreTransportItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //模板编号
        if ($transport_type_id = $this->request->get('transport_type_id')) {
            $query->where('transport_type_id', '=', $transport_type_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('transport_item_id', 'DESC');
    }

}
