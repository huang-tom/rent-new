<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class PageCategoryNavValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'category_nav_name' => 'required|string|max:50',
            'category_nav_order' => 'numeric|between:0,255'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'category_nav_name' => 'required|string|max:50',
            'category_nav_order' => 'numeric|between:0,255'
        ]
    ];

    protected $messages = [
        'category_nav_name.required' => '导航标题不能为空',
        'category_nav_name.max' => '导航标题长度不能超过50个字符',
        'category_nav_order.numeric' => '请输入正确的排序值',
        'category_nav_order.between' => '排序值范围0~255'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
