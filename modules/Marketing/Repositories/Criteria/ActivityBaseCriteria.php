<?php

namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动名称
        if ($activity_name = $this->request->get('activity_name')) {
            $query->where('activity_name', 'like', "%$activity_name%");
        }

        //活动状态
        $activity_state = $this->request->get('activity_state');
        if ($activity_state) {
            if (is_array($activity_state)) {
                $query->whereIn('activity_state', $activity_state);
            } else {
                $query->where('activity_state', '=', $activity_state);
            }
        }

        //活动类型
        if ($activity_type_id = $this->request->get('activity_type_id')) {
            if (is_array($activity_type_id)) {
                $query->whereIn('activity_type_id', $activity_type_id);
            } else {
                $query->where('activity_type_id', '=', $activity_type_id);
            }
        }

        //参与类型
        $activity_type = $this->request->get('activity_type');
        if ($activity_type) {
            if (is_array($activity_type)) {
                $query->whereIn('activity_type', $activity_type);
            } else {
                $query->where('activity_type', '=', $activity_type);
            }
        }

    }

    protected function after($model)
    {
        return $model->orderBy('activity_sort', 'ASC')->orderBy('activity_id', 'DESC');
    }

}
