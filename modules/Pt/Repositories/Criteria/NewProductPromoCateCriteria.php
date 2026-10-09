<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class NewProductPromoCateCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        $query->where('is_deleted', 0);

        if ($cate_name = $this->request->get('cate_name')) {
            $query->where('cate_name', 'like', '%' . $cate_name . '%');
        }

        if ($this->request->has('cate_type') && $this->request->get('cate_type') !== '') {
            $query->where('cate_type', $this->request->get('cate_type'));
        }

        if ($this->request->has('cate_enable') && $this->request->get('cate_enable') !== '') {
            $query->where('cate_enable', $this->request->get('cate_enable'));
        }
    }

    protected function after($model)
    {
        return $model->orderBy('cate_sort', 'ASC')->orderBy('cate_id', 'ASC');
    }
}
