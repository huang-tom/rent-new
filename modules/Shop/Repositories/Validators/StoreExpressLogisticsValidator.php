<?php

namespace Modules\Shop\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class StoreExpressLogisticsValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'logistics_name' => 'required|string|max:30',
            'express_id'     => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'logistics_name' => 'required|string|max:30',
            'express_id'     => 'required'
        ]
    ];

    protected $messages = [
        'logistics_name.required' => '物流名称不能为空',
        'logistics_name.max'      => '物流名称长度不能超过30个字符',

        'express_id.required'     => '请选择快递公司'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
