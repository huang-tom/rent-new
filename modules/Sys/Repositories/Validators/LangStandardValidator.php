<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class LangStandardValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'zh_cn' => 'required|string|max:30|unique:sys_lang_standard'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'zh_cn' => 'required|string|max:30|unique:sys_lang_standard,zh_CN,{zh_CN},zh_CN',
        ],
    ];

    protected $messages = [
        'zh_cn.required' => '中文不能为空',
        'zh_cn.max' => '中文长度不能超过2000个字符',
        'zh_cn.unique' => '该中文已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
