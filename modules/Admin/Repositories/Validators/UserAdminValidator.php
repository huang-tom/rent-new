<?php

namespace Modules\Admin\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserAdminValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'user_id' => 'required',
            'role_id' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'user_id' => 'required',
            'role_id' => 'required'
        ],
    ];

    protected $messages = [
        'user_id.required' => '用户编号不能为空',
        'role_id.required' => '请选择权限角色'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
