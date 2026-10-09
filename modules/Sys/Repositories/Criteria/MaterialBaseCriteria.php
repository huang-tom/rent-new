<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class MaterialBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //相册编号
        if ($gallery_id = $this->request->get('gallery_id')) {
            $query->where('gallery_id', '=', $gallery_id);
        }

        //素材类型
        if ($material_type = $this->request->get('material_type')) {
            $query->where('material_type', '=', $material_type);
        }

        //名称
        if ($material_name = $this->request->get('material_name')) {
            $query->where('material_name', 'like', "%$material_name%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('material_sort', 'ASC')->orderBy('material_id', 'DESC');
    }

}
