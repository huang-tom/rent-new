<?php

namespace Modules\Marketing\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ActivityBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'activity_type_id' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'activity_name' => 'required',
            'activity_type_id' => 'required',
        ]
    ];

    protected $messages = [
        'activity_type_id.required' => '活动类型不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
