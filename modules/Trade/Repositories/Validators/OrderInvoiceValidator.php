<?php

namespace Modules\Trade\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class OrderInvoiceValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'order_id' => 'required|string',
            'invoice_title' => 'required|string'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'order_id' => 'required|string',
            'invoice_title' => 'required|string',
            'order_invoice_id' => 'required|string'
        ]
    ];

    protected $messages = [
        'order_id.required' => '订单号不能为空',
        'invoice_title.required' => '发票抬头不能为空',
        'order_invoice_id.required' => '发票编号不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
