<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductSpecValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'spec_name' => 'required|string|max:20',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'spec_name' => 'required|string|max:20',
        ],
    ];

    protected $messages = [
        'spec_name.required' => '规格名称不能为空',
        'spec_name.max' => '规格名称长度不能超过20个字符',
        'spec_name.unique' => '规格名称已存在，请勿重复添加！'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
