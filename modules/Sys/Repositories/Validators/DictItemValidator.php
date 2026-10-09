<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class DictItemValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'dict_id' => 'required|string|max:32',
            'dict_item_name' => 'required|string|max:32|unique:sys_dict_item',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'dict_id' => 'required|string|max:50',
            'dict_item_name' => 'required|string|max:50|unique:sys_dict_item,dict_item_name,{dict_item_id},dict_item_id',
        ],
    ];

    protected $messages = [
        'dict_id.required' => '字典类型编号不能为空',
        'dict_id.max' => '字典类型编号长度不能超过32个字符',
        'dict_item_name.required' => '字典项名称不能为空',
        'dict_item_name.max' => '字典项名称长度不能超过50个字符',
        'dict_item_name.unique' => '字典项名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
