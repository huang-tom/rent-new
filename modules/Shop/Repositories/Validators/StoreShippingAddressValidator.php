<?php

namespace Modules\Shop\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class StoreShippingAddressValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'ss_name' => 'required|string|max:30',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'ss_name' => 'required|string|max:30',
        ],
    ];

    protected $messages = [
        'ss_name.required' => '联系人不能为空',
        'ss_name.max' => '联系人长度不能超过30个字符',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }


}
