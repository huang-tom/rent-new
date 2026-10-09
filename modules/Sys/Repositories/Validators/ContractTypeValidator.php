<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ContractTypeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'contract_type_name' => 'required|string|max:50|unique:sys_contract_type',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'contract_type_name' => 'required|string|max:50|unique:sys_contract_type,contract_type_name,{contract_type_id},contract_type_id',
        ]
    ];

    protected $messages = [
        'contract_type_name.required' => '保障名称不能为空',
        'contract_type_name.max' => '保障名称长度不能超过50个字符',
        'contract_type_name.unique' => '保障名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
