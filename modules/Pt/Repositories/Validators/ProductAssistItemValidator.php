<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductAssistItemValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'assist_id' => 'required',
            'assist_item_name' => 'required|string|max:100|unique:pt_product_assist_item',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'assist_id' => 'required',
            'assist_item_name' => 'required|string|max:100|unique:pt_product_assist_item,assist_item_name,{assist_item_id},assist_item_id',
        ],
    ];

    protected $messages = [
        'assist_id.required' => '属性编号不能为空',
        'assist_item_name.required' => '选项名称不能为空',
        'assist_item_name.max' => '选项名称长度不能超过100个字符',
        'assist_item_name.unique' => '选项名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
