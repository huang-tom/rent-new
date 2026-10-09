<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class MaterialGalleryCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //编号
        if ($gallery_id = $this->request->get('gallery_id')) {
            $query->where('gallery_id', '=', $gallery_id);
        }

        //类型
        if ($gallery_type = $this->request->get('gallery_type')) {
            $query->where('gallery_type', '=', $gallery_type);
        }

        //名称
        if ($gallery_name = $this->request->get('gallery_name')) {
            $query->where('gallery_name', 'like', "%$gallery_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('gallery_sort', 'ASC')->orderBy('gallery_id', 'DESC');
    }

}
