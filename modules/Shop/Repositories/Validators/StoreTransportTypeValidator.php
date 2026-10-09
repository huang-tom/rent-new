<?php

namespace Modules\Shop\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class StoreTransportTypeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'transport_type_name' => 'required|string|max:20',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'transport_type_name' => 'required|string|max:20',
        ]
    ];

    protected $messages = [
        'transport_type_name.required' => '模板名称不能为空',
        'transport_type_name.max'      => '模板名称长度不能超过20个字符',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
