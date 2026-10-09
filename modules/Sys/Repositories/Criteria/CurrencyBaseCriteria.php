<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class CurrencyBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //默认汇率
        if ($currency_is_default = $this->request->get('currency_is_default')) {
            $query->where('currency_is_default', '=', $currency_is_default);
        }

        //默认语言
        if ($currency_default_lang = $this->request->get('currency_default_lang')) {
            $query->where('currency_default_lang', '=', $currency_default_lang);
        }

        //默认UI
        if ($currency_is_standard = $this->request->get('currency_is_standard')) {
            $query->where('currency_is_standard', '=', $currency_is_standard);
        }

        //主键
        if ($currency_id = $this->request->get('currency_id')) {
            $query->where('currency_id', '=', $currency_id);
        }

        //是否开启
        if ($currency_status = $this->request->get('currency_status')) {
            $query->where('currency_status', '=', $currency_status);
        }

        //名称
        if ($currency_title = $this->request->get('currency_title')) {
            $query->where('currency_title', 'like', "%$currency_title%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('currency_sort', 'ASC')->orderBy('currency_id', 'ASC');
    }

}
