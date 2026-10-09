<?php

namespace Modules\Marketing\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ActivityItemValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'activity_id' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'activity_id' => 'required',
            'item_id' => 'required'
        ]
    ];

    protected $messages = [
        'activity_id.required' => '活动编号不能为空',
        'item_id.required' => '商品编号不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
