<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductSpecItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //规格ID
        if ($spec_id = $this->request->get('spec_id')) {
            $query->where('spec_id', '=', $spec_id);
        }

        //名称
        if ($spec_item_name = $this->request->get('spec_item_name')) {
            $query->where('spec_item_name', 'like', '%' . $spec_item_name . '%');
        }

    }

    protected function after($model)
    {
        return $model->orderBy('spec_item_sort', 'ASC')->orderBy('spec_item_id', 'ASC');
    }

}
