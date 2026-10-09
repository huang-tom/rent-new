<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class NewProductRentPeriodCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        // 列表不展示已删除档位；商品历史定价仍可通过 period_id 查到名称
        $query->where('is_deleted', 0);

        if ($period_name = $this->request->get('period_name')) {
            $query->where('period_name', 'like', '%' . $period_name . '%');
        }

        if ($this->request->has('period_enable') && $this->request->get('period_enable') !== '') {
            $query->where('period_enable', $this->request->get('period_enable'));
        }
    }

    protected function after($model)
    {
        return $model->orderBy('period_order', 'ASC')->orderBy('period_id', 'ASC');
    }
}
