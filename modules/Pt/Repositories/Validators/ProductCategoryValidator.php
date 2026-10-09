<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductCategoryValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'category_name' => 'required|string|max:50',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'category_name' => 'required|string|max:50',
        ],
    ];

    protected $messages = [
        'category_name.required' => '分类名称不能为空',
        'category_name.max' => '分类名称长度不能超过50个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
