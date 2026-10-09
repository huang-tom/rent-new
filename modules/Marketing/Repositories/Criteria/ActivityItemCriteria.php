<?php

namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityItemCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动ID
        if ($activity_id = $this->request->get('activity_id')) {
            $query->where('activity_id', '=', $activity_id);
        }

        //活动类型数组
        if ($activity_type_ids = $this->request->get('activity_type_ids')) {
            $query->whereIn('activity_type_id', $activity_type_ids);
        }

        //活动状态
        $activity_item_state = $this->request->get('activity_item_state');
        if ($activity_item_state) {
            if (is_array($activity_item_state)) {
                $query->whereIn('activity_item_state', $activity_item_state);
            } else {
                $query->where('activity_item_state', '=', $activity_item_state);
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

    }

    protected function after($model)
    {
        return $model->orderBy('activity_item_id', 'DESC');
    }
}
