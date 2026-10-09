<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ConfigBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'config_key' => 'required|string|max:50|unique:sys_config_base',
            'config_datatype' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'config_key' => 'required|string|max:50|unique:sys_config_base,config_key,{config_key},config_key',
            'config_datatype' => 'required'
        ]
    ];

    protected $messages = [
        'config_key.required' => '配置编码不能为空',
        'config_key.max' => '配置编码长度不能超过50个字符',
        'config_key.unique' => '配置编码已存在，请勿重复添加！',
        'config_datatype.required' => '选择数据配置类型'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
