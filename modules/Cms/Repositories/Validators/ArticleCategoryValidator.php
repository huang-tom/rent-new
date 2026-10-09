<?php

namespace Modules\Cms\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ArticleCategoryValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'category_name' => 'required|string|max:50|unique:cms_article_category',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'category_name' => 'required|string|max:50|unique:cms_article_category,category_name,{category_id},category_id',
        ],
    ];

    protected $messages = [
        'category_name.required' => '分类名称不能为空',
        'category_name.max' => '分类名称长度不能超过50个字符',
        'category_name.unique' => '分类名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
