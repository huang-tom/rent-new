<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserTagBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($tag_name = $this->request->get('tag_name')) {
            $query->where('tag_name', 'like', "%$tag_name%");
        }

        if ($tag_group_id = $this->request->get('tag_group_id')) {
            $query->where('tag_group_id', '=', $tag_group_id);
        }
    }

    protected function after($model)
    {
    	return $model->orderBy('tag_sort', 'ASC');
    }
}
