<?php

namespace Modules\Admin\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserRoleValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'user_role_name' => 'required|string|max:50',
            'user_role_code' => 'required|string|max:50|unique:admin_user_role'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'user_role_name' => 'required|string|max:50',
            'user_role_code' => 'required|string|max:50|unique:admin_user_role,user_role_code,{user_role_id},user_role_id',
        ],
    ];

    protected $messages = [
        'user_role_name.required' => '角色名称不能为空',
        'user_role_name.max' => '角色名称长度不能超过50个字符',
        'user_role_code.required' => '角色标识不能为空',
        'user_role_code.max' => '角色标识长度不能超过50个字符',
        'user_role_code.unique' => '角色标识已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
