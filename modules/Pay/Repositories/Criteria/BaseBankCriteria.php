<?php

namespace Modules\Pay\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class BaseBankCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //银行名称
        if ($bank_name = $this->request->get('bank_name')) {
            $query->where('bank_name', 'like', '%' . $bank_name . '%');
        }

    }

    protected function after($model)
    {
        return $model->orderBy('bank_order', 'ASC')->orderBy('bank_id', 'ASC');
    }
}
