<?php

namespace Modules\Trade\Services;

use App\Support\StateCode;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;

/**
 * Class OrderInfoService.
 *
 * @package Modules\Trade\Services
 */
class OrderInfoService extends BaseService
{

    private $configBaseRepository;

    public function __construct(OrderInfoRepository $orderInfoRepository, ConfigBaseRepository $configBaseRepository)
    {
        $this->repository = $orderInfoRepository;
        $this->configBaseRepository = $configBaseRepository;
    }


    /**
     * 自动取消订单
     * @return void
     */
    public function autoCancelOrder()
    {
        $order_ids = $this->getAutoCancelOrderId();
        if (!empty($order_ids)) {
            $orderService = app(OrderService::class);
            foreach ($order_ids as $order_id) {
                $orderService->cancel($order_id, __('超时未支付，系统自动取消'));
            }
        }
    }


    /**
     * 获取需要自动取消的订单ID
     * @return array|mixed[]
     */
    public function getAutoCancelOrderId()
    {
        $auto_cancel_time = $this->configBaseRepository->getConfig('order_autocancel_time', 24); //单位：小时
        $time = getTime();
        $column_row = [
            'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY,
            'order_is_paid' => StateCode::ORDER_PAID_STATE_NO,
            'payment_type_id' => StateCode::PAYMENT_TYPE_ONLINE,
            ['create_time', '<', ($time - $auto_cancel_time * 60 * 60 * 1000)]
        ];

        return $this->repository->findKey($column_row);
    }


    /**
     * 自动确认收货
     * @return void
     */
    public function autoReceive()
    {
        $order_ids = $this->getAutoFinishOrderId();
        if (!empty($order_ids)) {
            $orderService = app(OrderService::class);
            foreach ($order_ids as $order_id) {
                $orderService->receive($order_id, __('系统自动收货'));
            }
        }
    }


    /**
     * 获取需要自动确认售后的订单ID
     * @return array|mixed[]
     */
    public function getAutoFinishOrderId()
    {
        $order_autofinish_time = $this->configBaseRepository->getConfig('order_autofinish_time', 7); //单位：天
        $time = getTime();
        $column_row = [
            ['order_state_id', 'IN', [StateCode::ORDER_STATE_SHIPPED, StateCode::ORDER_STATE_RECEIVED]],
            ['update_time', '<', ($time - $order_autofinish_time * 60 * 60 * 24 * 1000)]
        ];

        return $this->repository->findKey($column_row);
    }

}
