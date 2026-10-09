<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ContractTypeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($contract_type_name = $this->request->get('contract_type_name')) {
            $query->where('contract_type_name', 'like', "%$contract_type_name%");
        }
    }

    protected function after($model)
    {
        return $model->orderBy('contract_type_order', 'ASC')->orderBy('contract_type_id', 'ASC');
    }

}
