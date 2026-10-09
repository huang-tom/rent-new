<?php

namespace Modules\Trade\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class OrderReturnReasonValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'return_reason_name' => 'required|string|max:100',
            'return_reason_sort' => 'numeric|between:0,255'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'return_reason_name' => 'required|string|max:100',
            'return_reason_sort' => 'numeric|between:0,255'
        ]
    ];

    protected $messages = [
        'return_reason_name.required' => '理由不能为空',
        'return_reason_name.max' => '理由长度不能超过100个字符',
        'return_reason_sort.numeric' => '请输入正确的排序值',
        'return_reason_sort.between' => '排序值范围0~255'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
