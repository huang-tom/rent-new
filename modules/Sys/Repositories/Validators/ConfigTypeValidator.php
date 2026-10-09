<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ConfigTypeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'config_type_name' => 'required|string|max:50|unique:sys_config_type',
            'config_type_module' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'config_type_name' => 'required|string|max:50|unique:sys_config_type,config_type_name,{config_type_id},config_type_id',
            'config_type_module' => 'required'
        ]
    ];

    protected $messages = [
        'config_type_name.required' => '分组名称不能为空',
        'config_type_name.max' => '分组名称长度不能超过50个字符',
        'config_type_name.unique' => '分组名称已存在，请勿重复添加！',
        'config_type_module.required' => '请选择所属模块',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
