<?php

namespace Modules\Sys\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class PagePcNavValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'nav_title' => 'required|string|max:100',
            'nav_order' => 'numeric|between:0,255'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'nav_title' => 'required|string|max:100',
            'nav_order' => 'numeric|between:0,255'
        ]
    ];

    protected $messages = [
        'nav_title.required' => '导航标题不能为空',
        'nav_title.max'      => '导航标题长度不能超过100个字符',
        'nav_order.numeric'  => '请输入正确的排序值',
        'nav_order.between'  => '排序值范围0~255'
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
