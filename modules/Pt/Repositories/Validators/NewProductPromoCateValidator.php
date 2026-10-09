<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class NewProductPromoCateValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'cate_name' => 'required|string|max:50',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'cate_name' => 'required|string|max:50',
        ],
    ];

    protected $messages = [
        'cate_name.required' => '分类名称不能为空',
        'cate_name.max' => '分类名称长度不能超过50个字符',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
