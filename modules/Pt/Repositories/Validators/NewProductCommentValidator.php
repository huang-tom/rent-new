<?php

namespace Modules\Pt\Repositories\Validators;

use App\Exceptions\ErrorException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class NewProductCommentValidator extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'product_name' => 'required|string|max:100',
            'user_name' => 'required|string|max:50',
            'comment_scores' => 'required|integer|min:1|max:5',
        ],
        ValidatorInterface::RULE_UPDATE => [
            'product_name' => 'required|string|max:100',
            'user_name' => 'required|string|max:50',
            'comment_scores' => 'required|integer|min:1|max:5',
        ],
    ];

    protected $messages = [
        'product_name.required' => '商品名称不能为空',
        'user_name.required' => '客户姓名不能为空',
        'comment_scores.required' => '评分不能为空',
        'comment_scores.min' => '评分最小为1',
        'comment_scores.max' => '评分最大为5',
    ];

    public function passesOrFail($action = null)
    {
        if (!$this->passes($action)) {
            throw new ErrorException($this->errors->first());
        }

        return true;
    }
}
