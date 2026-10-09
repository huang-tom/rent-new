<?php

namespace Modules\O2o\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class ChainItemValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'chain_id' => 'required|integer',
            'item_id'  => 'required|integer',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'chain_id' => 'required|integer',
        ],
    ];

    protected $messages = [
        'chain_id.required' => '所属门店不能为空',
        'chain_id.integer'  => '所属门店参数有误',
        'item_id.required'  => '商品不能为空',
        'item_id.integer'   => '商品参数有误',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
