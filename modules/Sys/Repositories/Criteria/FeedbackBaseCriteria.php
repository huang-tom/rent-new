<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class FeedbackBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //名称
        if ($feedback_question = $this->request->get('feedback_question')) {
            $query->where('feedback_question', 'like', "%$feedback_question%");
        }

        //反馈分类ID
        if ($feedback_category_id = $this->request->get('feedback_category_id')) {
            $query->where('feedback_category_id', '=', $feedback_category_id);
        }

        //用户昵称
        if ($user_nickname = $this->request->get('user_nickname')) {
            $query->where('user_nickname', 'like', "%$user_nickname%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }
    }


    protected function after($model)
    {
        return $model->orderBy('feedback_id', 'DESC');
    }

}
