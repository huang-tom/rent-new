<?php

namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserInvoiceCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //发票抬头
        if ($invoice_title = $this->request->get('invoice_title')) {
            $query->where('invoice_title', 'like', "%$invoice_title%");
        }

        //纳税人识别号
        if ($invoice_company_code = $this->request->get('invoice_company_code')) {
            $query->where('invoice_company_code', 'like', "%$invoice_company_code%");
        }

        //联系电话
        if ($invoice_phone = $this->request->get('invoice_phone')) {
            $query->where('invoice_phone', 'like', "%$invoice_phone%");
        }

        //用户ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //公司开票(BOOL):0-个人;1-公司
        if ($invoice_is_company = $this->request->get('invoice_is_company')) {
            $query->where('invoice_is_company', '=', $invoice_is_company);
        }

    }

    protected function after($model)
    {
       return $model->orderBy('user_invoice_id', 'DESC');
    }
}
