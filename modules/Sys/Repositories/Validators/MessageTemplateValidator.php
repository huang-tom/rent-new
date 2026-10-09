<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class MessageTemplateValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'message_name' => 'required|string|max:30|unique:sys_message_template',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'message_name' => 'required|string|max:30|unique:sys_message_template,message_name,{message_id},message_id',
        ],
    ];

    protected $messages = [
        'message_name.required' => '模板名称不能为空',
        'message_name.max'      => '模板名称长度不能超过30个字符',
        'message_name.unique'   => '模板名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
