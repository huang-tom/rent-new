<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'product_name' => 'required|string|max:100',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'product_name' => 'required|string|max:100',
        ],
    ];

    protected $messages = [
        'product_name.required' => '商品名称不能为空',
        'product_name.max' => '商品名称长度不能超过100个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
