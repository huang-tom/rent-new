<?php

namespace App\Support;

class LevelCode
{
    public const EXP_TYPE_REG = 1;  //会员注册
    public const EXP_TYPE_LOGIN = 2;  //会员登录
    public const EXP_TYPE_EVALUATE_PRODUCT = 3; //商品评论
    public const EXP_TYPE_EVALUATE_STORE = 6; //店铺评论
    public const EXP_TYPE_CONSUME = 4; //购买商品
    public const EXP_TYPE_OTHER = 5; //管理员操作
    public const EXP_TYPE_EXCHANGE_PRODUCT = 7; //积分换购商品
    public const EXP_TYPE_EXCHANGE_VOUCHER = 8; //积分兑换优惠券
}
