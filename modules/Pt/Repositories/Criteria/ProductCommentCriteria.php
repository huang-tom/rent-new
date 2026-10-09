<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ProductCommentCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //商品名称
        if ($item_name = $this->request->get('item_name')) {
            $query->where('item_name', 'like', '%' . $item_name . '%');
        }

        //商品ID
        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', '=', $product_id);
        }

        //商品SKU
        if ($item_id = $this->request->get('item_id')) {
            $query->where('item_id', '=', $item_id);
        }

        //comment_scores
        if ($comment_scores = $this->request->get('comment_scores')) {
            $query->where('comment_scores', '=', $comment_scores);
        }

        //评分类型筛选
        $comment_type = $this->request->get('comment_type', 0);
        if ($comment_type == 1) {
            $query->where('comment_scores', '>', 3);
        } elseif ($comment_type == 2) {
            $query->where('comment_scores', '=', 3);
        } elseif ($comment_type == 3) {
            $query->where('comment_scores', '<', 3);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('comment_id', 'DESC');
    }

}
