<?php

namespace Modules\O2o\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ChainBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($chain_name = $this->request->get('chain_name')) {
            $query->where('chain_name', 'like', "%$chain_name%");
        }

        if ($chain_category_id = $this->request->get('chain_category_id')) {
            $query->where('chain_category_id', '=', $chain_category_id);
        }

        if ($store_id = $this->request->get('store_id')) {
            $query->where('store_id', '=', $store_id);
        }

        if ($chain_id = $this->request->get('chain_id')) {
            $query->where('chain_id', '=', $chain_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('chain_id', 'ASC');
    }

}
