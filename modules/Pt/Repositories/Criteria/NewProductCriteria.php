<?php

namespace Modules\Pt\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;
use Modules\Pt\Services\NewProductService;

class NewProductCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        // 列表不展示已删除商品
        $query->where('is_deleted', 0);

        // 关键字：商品名称 / 商品编号
        $keyword = $this->request->get('keyword', '');
        if ($keyword === '' || $keyword === null) {
            $keyword = $this->request->get('product_name', '');
        }
        if ($keyword !== '' && $keyword !== null) {
            $query->where(function ($q) use ($keyword) {
                $q->where('product_name', 'like', '%' . $keyword . '%')
                    ->orWhere('product_number', 'like', '%' . $keyword . '%');
            });
        }

        if ($category_id = $this->request->get('category_id')) {
            $query->where('category_id', $category_id);
        }

        if ($this->request->has('sale_mode') && $this->request->get('sale_mode') !== '') {
            $query->where('sale_mode', $this->request->get('sale_mode'));
        }

        // 状态筛选：1上架 / 0下架 / 2待审核
        if ($this->request->has('status') && $this->request->get('status') !== '') {
            $status = (int)$this->request->get('status');
            if ($status === NewProductService::LIST_STATUS_ON) {
                $query->where('product_state', NewProductService::PRODUCT_STATE_ON)
                    ->where('audit_status', NewProductService::AUDIT_STATUS_PASSED);
            } elseif ($status === NewProductService::LIST_STATUS_OFF) {
                $query->where('product_state', NewProductService::PRODUCT_STATE_OFF)
                    ->where('audit_status', NewProductService::AUDIT_STATUS_PASSED);
            } elseif ($status === NewProductService::LIST_STATUS_PENDING) {
                $query->where('audit_status', NewProductService::AUDIT_STATUS_PENDING);
            }
        }
    }

    protected function after($model)
    {
        return $model->orderBy('product_id', 'DESC');
    }
}
