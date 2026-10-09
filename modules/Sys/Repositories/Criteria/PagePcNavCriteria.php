<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class PagePcNavCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($nav_title = $this->request->get('nav_title')) {
            $query->where('nav_title', 'like', "%$nav_title%");
        }

    }


    protected function after($model)
    {
        return $model->orderBy('nav_order', 'ASC')->orderBy('nav_id', 'ASC');
    }

}
