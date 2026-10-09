<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ProductSpecItemValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'spec_item_name' => 'required',
            'spec_id' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'spec_item_name' => 'required',
            'spec_id' => 'required'
        ],
    ];

    protected $messages = [
        'spec_item_name.required' => '规格值名称不能为空',
        'spec_id.required' => '规格编号不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
