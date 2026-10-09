<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class FeedbackCategoryValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'feedback_category_name' => 'required|string|max:50|unique:sys_feedback_category',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'feedback_category_name' => 'required|string|max:50|unique:sys_feedback_category,feedback_category_name,{feedback_category_id},feedback_category_id',
        ]
    ];

    protected $messages = [
        'feedback_category_name.required' => '分类名称不能为空',
        'feedback_category_name.max'      => '分类名称长度不能超过50个字符',
        'feedback_category_name.unique'   => '分类名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
