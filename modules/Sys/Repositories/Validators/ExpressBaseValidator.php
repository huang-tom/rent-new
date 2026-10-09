<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ExpressBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'express_name' => 'required|string|max:30|unique:sys_express_base',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'express_name' => 'required|string|max:30|unique:sys_express_base,express_name,{express_id},express_id',
        ],
    ];

    protected $messages = [
        'express_name.required' => '快递名称不能为空',
        'express_name.max'      => '快递名称长度不能超过30个字符',
        'express_name.unique'   => '快递名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
