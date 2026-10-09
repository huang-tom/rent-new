<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductTypeValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'type_name' => 'required|string|max:20|unique:pt_product_type',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'type_name' => 'required|string|max:20|unique:pt_product_type,type_name,{type_id},type_id',
        ],
    ];

    protected $messages = [
        'type_name.required' => '类型名称不能为空',
        'type_name.max' => '类型名称长度不能超过20个字符',
        'type_name.unique' => '类型名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
