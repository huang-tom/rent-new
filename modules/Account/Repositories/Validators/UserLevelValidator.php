<?php

namespace Modules\Account\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserLevelValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'user_level_name' => 'required|string|max:50|unique:account_user_level',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'user_level_name' => 'required|string|max:50|unique:account_user_level,user_level_name,{user_level_id},user_level_id',
        ]
    ];

    protected $messages = [
        'user_level_name.required' => '等级名称不能为空',
        'user_level_name.max'      => '等级名称长度不能超过50个字符',
        'user_level_name.unique'   => '等级名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
