<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductBrandValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'brand_name' => 'required|string|max:50|unique:pt_product_brand',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'brand_name' => 'required|string|max:50|unique:pt_product_brand,brand_name,{brand_id},brand_id',
        ],
    ];

    protected $messages = [
        'brand_name.required' => '品牌名称不能为空',
        'brand_name.max' => '品牌名称长度不能超过50个字符',
        'brand_name.unique' => '品牌名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
