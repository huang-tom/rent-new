<?php

namespace Modules\Account\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserInvoiceValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'invoice_title' => 'required|string|max:50',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'invoice_title' => 'required|string|max:50',
        ]
    ];

    protected $messages = [
        'invoice_title.required' => '发票抬头不能为空',
        'invoice_title.max' => '发票抬头长度不能超过50个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
