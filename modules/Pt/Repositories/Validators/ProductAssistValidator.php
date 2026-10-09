<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductAssistValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'assist_name' => 'required|string|max:20|unique:pt_product_assist',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'type_id' => 'required',
            'assist_name' => 'required|string|max:20|unique:pt_product_assist,assist_name,{assist_id},assist_id',
        ],
    ];

    protected $messages = [
        'assist_name.required' => '属性名称不能为空',
        'assist_name.max' => '属性名称长度不能超过20个字符',
        'assist_name.unique' => '属性名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
