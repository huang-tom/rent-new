<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class DictBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'dict_id' => 'required|string|max:32|unique:sys_dict_base',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'dict_id' => 'required|string|max:32|unique:sys_dict_base,dict_id,{dict_id},dict_id',
        ],
    ];

    protected $messages = [
        'dict_id.required' => '主键编号不能为空',
        'dict_id.max' => '主键编号长度不能超过32个字符',
        'dict_id.unique' => '主键编号已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
