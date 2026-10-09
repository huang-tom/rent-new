<?php

namespace Modules\Cms\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ArticleTagCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        if ($tag_name = $this->request->get('tag_name')) {
            $query->where('tag_name', 'like', "%$tag_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('tag_id', 'ASC');
    }

}
