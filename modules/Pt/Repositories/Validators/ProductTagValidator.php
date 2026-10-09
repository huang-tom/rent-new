<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductTagValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'product_tag_name' => 'required|string|max:100|unique:pt_product_tag',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'product_tag_name' => 'required|string|max:100|unique:pt_product_tag,product_tag_name,{product_tag_id},product_tag_id',
        ],
    ];

    protected $messages = [
        'product_tag_name.required' => '名称不能为空',
        'product_tag_name.max' => '名称长度不能超过100个字符',
        'product_tag_name.unique' => '名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
