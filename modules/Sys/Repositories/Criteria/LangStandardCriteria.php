<?php

namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class LangStandardCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //默认中文语言
        if ($zh_CN = $this->request->get('zh_CN')) {
            $query->where('zh_CN', 'LIKE', '%' . $zh_CN . '%');
        }

        //重要文字
        if ($is_imp = $this->request->get('is_imp')) {
            $query->where('is_imp', '=', $is_imp);
        }

        //是否启用
        if ($is_used = $this->request->get('is_used')) {
            $query->where('is_used', '=', $is_used);
        }

        //前端启用
        if ($frontend = $this->request->get('frontend')) {
            $query->where('frontend', '=', $frontend);
        }

        //后端启用
        if ($backend = $this->request->get('backend')) {
            $query->where('backend', '=', $backend);
        }

        //语言内容搜索 模糊查询字段
        $likeFields = ['zh_TW', 'en_GB', 'th_TH', 'es_MX', 'ar_SA', 'vi_VN', 'tr_TR', 'ja_JP', 'id_ID', 'de_DE', 'fr_FR', 'pt_PT', 'it_IT',
            'ru_RU', 'ro_RO', 'az_AZ', 'el_GR', 'fi_FI', 'lv_LV', 'nl_NL', 'da_DK', 'sr_RS', 'pl_PL', 'uk_UA', 'kk_KZ', 'my_MM', 'ko_KR', 'ms_MY'];
        foreach ($likeFields as $field) {
            if ($value = $this->request->get($field)) {
                $query->where($field, 'LIKE', '%' . $value . '%');
            }
        }

    }


    protected function after($model)
    {
        return $model->orderBy('time', 'DESC');
    }

}
