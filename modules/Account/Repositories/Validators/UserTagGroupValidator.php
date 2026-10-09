<?php

namespace Modules\Account\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserTagGroupValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'tag_group_name' => 'required|string|max:50|unique:account_user_tag_group',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'tag_group_name' => 'required|string|max:50|unique:account_user_tag_group,tag_group_name,{tag_group_id},tag_group_id',
        ],
    ];

    protected $messages = [
        'tag_group_name.required' => '分组名称不能为空',
        'tag_group_name.max'      => '分组名称长度不能超过50个字符',
        'tag_group_name.unique'   => '分组名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
