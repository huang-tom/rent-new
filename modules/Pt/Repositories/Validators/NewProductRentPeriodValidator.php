<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class NewProductRentPeriodValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'period_name' => 'required|string|max:50',
            'period_days' => 'required|integer|min:1',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'period_name' => 'required|string|max:50',
            'period_days' => 'required|integer|min:1',
        ],
    ];

    protected $messages = [
        'period_name.required' => '档位名称不能为空',
        'period_name.max' => '档位名称长度不能超过50个字符',
        'period_days.required' => '天数不能为空',
        'period_days.min' => '天数必须大于0',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
