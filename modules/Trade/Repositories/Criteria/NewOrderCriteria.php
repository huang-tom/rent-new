<?php

namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class NewOrderCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        $query->where('is_deleted', 0);

        $keyword = $this->request->get('keyword', '');
        if ($keyword !== '' && $keyword !== null) {
            $query->where(function ($q) use ($keyword) {
                $q->where('order_number', 'like', '%' . $keyword . '%')
                    ->orWhere('buyer_message', 'like', '%' . $keyword . '%')
                    ->orWhereIn('user_id', function ($sub) use ($keyword) {
                        $sub->select('user_id')
                            ->from('account_user_info')
                            ->where(function ($u) use ($keyword) {
                                $u->where('user_nickname', 'like', '%' . $keyword . '%')
                                    ->orWhere('user_mobile', 'like', '%' . $keyword . '%');
                            });
                    })
                    ->orWhereIn('order_id', function ($sub) use ($keyword) {
                        $sub->select('order_id')
                            ->from('trade_new_order_item')
                            ->where('product_name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ($this->request->has('order_source') && $this->request->get('order_source') !== '') {
            $query->where('order_source', $this->request->get('order_source'));
        }

        if ($this->request->has('order_status') && $this->request->get('order_status') !== '') {
            $query->where('order_status', (int)$this->request->get('order_status'));
        }

        $start = $this->request->get('order_time_start', '');
        if ($start !== '' && $start !== null) {
            $query->where('order_time', '>=', $start);
        }
        $end = $this->request->get('order_time_end', '');
        if ($end !== '' && $end !== null) {
            $query->where('order_time', '<=', $end);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('order_id', 'DESC');
    }
}
