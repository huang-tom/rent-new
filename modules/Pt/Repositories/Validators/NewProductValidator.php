<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class NewProductValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'product_name' => 'required|string|max:100',
            'sale_mode' => 'required|integer|in:1,2,3',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'product_name' => 'required|string|max:100',
            'sale_mode' => 'required|integer|in:1,2,3',
        ],
    ];

    protected $messages = [
        'product_name.required' => '商品名称不能为空',
        'product_name.max' => '商品名称长度不能超过100个字符',
        'sale_mode.required' => '销售方式不能为空',
        'sale_mode.in' => '销售方式不正确',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
