<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductAssistItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($assist_item_name = $this->request->get('assist_item_name')) {
            $query->where('assist_item_name', 'like', '%' . $assist_item_name . '%');
        }

        //属性ID
        if ($assist_id = $this->request->get('assist_id')) {
            $query->where('assist_id', '=', $assist_id);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('assist_item_sort', 'ASC')->orderBy('assist_item_id', 'DESC');
    }

}
