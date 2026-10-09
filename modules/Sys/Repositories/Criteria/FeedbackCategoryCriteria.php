<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class FeedbackCategoryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //分类名称
        if ($feedback_category_name = $this->request->get('feedback_category_name')) {
            $query->where('feedback_category_name', 'like', "%$feedback_category_name%");
        }

        //反馈类型ID
        if ($feedback_type_id = $this->request->get('feedback_type_id')) {
            $query->where('feedback_type_id', '=', $feedback_type_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('feedback_category_id', 'ASC');
    }

}
