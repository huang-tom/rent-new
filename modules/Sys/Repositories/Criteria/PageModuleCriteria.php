<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class PageModuleCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //页面ID
        if ($page_id = $this->request->get('page_id')) {
            $query->where('page_id', '=', $page_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('pm_order', 'ASC')->orderBy('pm_id', 'ASC');
    }

}
