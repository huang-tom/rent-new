<?php

namespace Modules\O2o\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ChainCategoryValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'chain_category_name' => 'required|string|max:20',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'chain_category_name' => 'required|string|max:20',
        ],
    ];

    protected $messages = [
        'chain_category_name.required' => '名称不能为空',
        'chain_category_name.max' => '名称长度不能超过50个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
