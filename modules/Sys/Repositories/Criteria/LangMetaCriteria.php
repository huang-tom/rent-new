<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class LangMetaCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //语言
        if ($meta_key = $this->request->get('meta_key')) {
            $query->where('meta_key', '=', $meta_key);
        }

        //待翻译内容
        if ($meta_ori = $this->request->get('meta_ori')) {
            $query->where('meta_ori', 'LIKE', '%' . $meta_ori . '%');
        }

        //翻译后内容
        if ($meta_value = $this->request->get('meta_value')) {
            $query->where('meta_value', 'LIKE', '%' . $meta_value . '%');
        }

        //表
        if ($table_name = $this->request->get('table_name')) {
            $query->where('table_name', '=', $table_name);
        }

    }


    protected function after($model)
    {
        return $model->orderBy('meta_id', 'DESC');
    }

}
