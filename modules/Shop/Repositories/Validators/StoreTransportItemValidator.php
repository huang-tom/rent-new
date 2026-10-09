<?php

namespace Modules\Shop\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class StoreTransportItemValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'transport_type_id' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'transport_item_id' => 'required',
        ]
    ];

    protected $messages = [
        'transport_type_id.required' => '模板编号不能为空',
        'transport_item_id.required' => '模板编号不能为空',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
