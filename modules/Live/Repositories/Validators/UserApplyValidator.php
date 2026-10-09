<?php

namespace Modules\Live\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class UserApplyValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'tag_name' => 'required|string|max:50|unique:cms_article_tag',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'tag_name' => 'required|string|max:50|unique:cms_article_tag,tag_name,{tag_id},tag_id',
        ],
    ];

    protected $messages = [
        'tag_name.required' => '标签名称不能为空',
        'tag_name.max' => '标签名称长度不能超过50个字符',
        'tag_name.unique' => '标签名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
