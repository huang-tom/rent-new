<?php

namespace Modules\Account\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserTagBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'tag_title' => 'required|string|max:50|unique:account_user_tag_base',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'tag_title' => 'required|string|max:50|unique:account_user_tag_base,tag_title,{tag_id},tag_id',
        ],
    ];

    protected $messages = [
        'tag_title.required' => '标签名称不能为空',
        'tag_title.max'      => '标签名称长度不能超过50个字符',
        'tag_title.unique'   => '标签名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
