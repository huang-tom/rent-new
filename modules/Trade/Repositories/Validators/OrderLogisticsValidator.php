<?php

namespace Modules\Trade\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class OrderLogisticsValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'order_id' => 'required|string',
            'stock_bill_id' => 'required|string',
            'logistics_id' => 'required',
            'order_tracking_number' => 'required',
            'ss_id' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [

        ]
    ];

    protected $messages = [
        'order_id.required'      => '订单号不能为空',
        'stock_bill_id.required' => '出库单号不能为空',
        'logistics_id.required'  => '请选择物流公司',
        'order_tracking_number.required' => '请填写物流单号',
        'ss_id.required' => '请选择发货地址',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
