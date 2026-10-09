<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class PageBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //页面名称
        if ($page_name = $this->request->get('page_name')) {
            $query->where('page_name', 'like', "%$page_name%");
        }

        //页面类型
        if ($page_type = $this->request->get('page_type')) {
            $query->where('page_type', '=', $page_type);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('page_id', 'ASC');
    }

}
