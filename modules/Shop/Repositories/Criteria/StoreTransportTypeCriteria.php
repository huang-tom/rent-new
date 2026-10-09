<?php

namespace Modules\Shop\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class StoreTransportTypeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //模板名称
        if ($transport_type_name = $this->request->get('transport_type_name')) {
            $query->where('transport_type_name', 'like', "%$transport_type_name%");
        }

        //计费规则
        if ($transport_type_pricing_method = $this->request->get('transport_type_pricing_method')) {
            $query->where('transport_type_pricing_method', '=', $transport_type_pricing_method);
        }

        //全免运费(BOOL):0-不全免;1-全免
        if ($transport_type_free = $this->request->get('transport_type_free')) {
            $query->where('transport_type_free', '=', $transport_type_free);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('transport_type_id', 'DESC');
    }

}
