<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class PageBaseValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'page_name' => 'required|string|max:20',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'page_name' => 'required|string|max:20',
        ]
    ];

    protected $messages = [
        'page_name.required' => '页面名称不能为空',
        'page_name.max'      => '页面名称长度不能超过20个字符'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
