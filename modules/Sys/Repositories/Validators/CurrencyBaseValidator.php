<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class CurrencyBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'currency_title' => 'required|string|max:30|unique:sys_currency_base',
            'currency_lang' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'currency_title' => 'required|string|max:30|unique:sys_currency_base,currency_title,{currency_id},currency_id',
        ],
    ];

    protected $messages = [
        'currency_title.required' => '货币名称不能为空',
        'currency_title.max' => '货币名称长度不能超过30个字符',
        'currency_title.unique' => '货币名称已存在，请勿重复添加！',
        'currency_lang.required' => '语言符号不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
