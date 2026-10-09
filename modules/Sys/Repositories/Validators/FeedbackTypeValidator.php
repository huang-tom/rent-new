<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class FeedbackTypeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'feedback_type_name' => 'required|string|max:50|unique:sys_feedback_type',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'feedback_type_name' => 'required|string|max:50|unique:sys_feedback_type,feedback_type_name,{feedback_type_id},feedback_type_id',
        ]
    ];

    protected $messages = [
        'feedback_type_name.required' => '类型名称不能为空',
        'feedback_type_name.max'      => '类型名称长度不能超过50个字符',
        'feedback_type_name.unique'   => '类型名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
