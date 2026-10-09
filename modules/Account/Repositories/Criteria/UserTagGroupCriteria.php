<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserTagGroupCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($tag_group_name = $this->request->get('tag_group_name')) {
            $query->where('tag_group_name', 'like', "%$tag_group_name%");
        }
    }

    protected function after($model)
    {
    	return $model->orderBy('tag_group_sort', 'ASC');
    }
}
