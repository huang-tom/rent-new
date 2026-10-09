<?php

namespace App\Support;


/**
 * 状态常量
 */
class ErrorTypeEnum
{
    const ERR_NOT_DEFINITION = array("label" => "未分类异常", "value" => 0);
    const ERR_WX_JSPI = array("label" => "微信JSPI异常", "value" => 2001);
    const ERR_WX_XCX = array("label" => "微信小程序异常", "value" => 2002);
    const ERR_WX_MP = array("label" => "微信公众号异常", "value" => 2003);
    const ERR_ALI_PAY = array("label" => "支付宝支付异常", "value" => 3001);
    const ERR_PSUH_MSG = array("label" => "消息推送异常", "value" => 4001);
    const ERR_ALI_SERVICE = array("label" => "阿里云服务异常", "value" => 5001);
    const ERR_TENCENT_SERVICE = array("label" => "腾讯云服务异常", "value" => 5002);
    const ERR_HUAWEI_SERVICE = array("label" => "华为云服务异常", "value" => 5003);
    const ERR_ORDER_SERVICE = array("label" => "订单处理异常", "value" => 6001);

    public function getAllValues()
    {
        $errorTypes = [];
        $refClass = new \ReflectionClass($this);
        $constants = $refClass->getConstants();

        foreach ($constants as $constant) {
            $errorTypes[] = $constant;
        }

        return $errorTypes;
    }
}
