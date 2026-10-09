<?php

namespace Modules\Shop\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class StoreShippingAddressCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //联系人
        if ($ss_name = $this->request->get('ss_name')) {
            $query->where('ss_name', 'like', "%$ss_name%");
        }

        //手机号码
        if ($ss_mobile = $this->request->get('ss_mobile')) {
            $query->where('ss_mobile', 'like', "%$ss_mobile%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('ss_id', 'DESC');
    }
}
