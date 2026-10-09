<?php

namespace Modules\Account\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserDeliveryAddressValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'ud_name' => 'required|string|max:20',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'ud_name' => 'required|string|max:20',
        ]
    ];

    protected $messages = [
        'ud_name.required' => '收件人名称不能为空',
        'ud_name.max' => '收件人名称长度不能超过50个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
