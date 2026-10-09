<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;
use Modules\Pt\Services\NewProductCommentService;

class NewProductCommentCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        $query->where('is_deleted', 0);

        // 关键词：评论内容 / 客户姓名 / 商品名称
        $keyword = $this->request->get('keyword', '');
        if ($keyword !== '' && $keyword !== null) {
            $query->where(function ($q) use ($keyword) {
                $q->where('comment_content', 'like', '%' . $keyword . '%')
                    ->orWhere('user_name', 'like', '%' . $keyword . '%')
                    ->orWhere('product_name', 'like', '%' . $keyword . '%');
            });
        }

        // 精确星级
        if ($this->request->has('comment_scores') && $this->request->get('comment_scores') !== '') {
            $query->where('comment_scores', (int)$this->request->get('comment_scores'));
        }

        // 评分筛选：5=五星;4=四星;3=三星及以下
        if ($this->request->has('score_type') && $this->request->get('score_type') !== '') {
            $score_type = (int)$this->request->get('score_type');
            if ($score_type === NewProductCommentService::SCORE_TYPE_5) {
                $query->where('comment_scores', 5);
            } elseif ($score_type === NewProductCommentService::SCORE_TYPE_4) {
                $query->where('comment_scores', 4);
            } elseif ($score_type === NewProductCommentService::SCORE_TYPE_3_BELOW) {
                $query->where('comment_scores', '<=', 3);
            }
        }

        // 审核状态
        if ($this->request->has('audit_status') && $this->request->get('audit_status') !== '') {
            $query->where('audit_status', (int)$this->request->get('audit_status'));
        }

        if ($product_id = $this->request->get('product_id')) {
            $query->where('product_id', (int)$product_id);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('comment_time', 'DESC')->orderBy('comment_id', 'DESC');
    }
}
