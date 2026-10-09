<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class PageModuleValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'page_id' => 'required',
            'module_id' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'page_id' => 'required',
            'module_id' => 'required'
        ]
    ];

    protected $messages = [
        'page_id.required' => '页面编号不能为空',
        'module_id.required' => '模板编号不能为空'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
