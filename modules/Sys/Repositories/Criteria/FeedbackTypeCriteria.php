<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class FeedbackTypeCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($feedback_type_name = $this->request->get('feedback_type_name')) {
            $query->where('feedback_type_name', 'like', "%$feedback_type_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('feedback_type_id', 'ASC');
    }

}
