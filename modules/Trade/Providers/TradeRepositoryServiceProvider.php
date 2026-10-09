<?php

namespace Modules\Trade\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class TradeRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //订单基础表
            \Modules\Trade\Repositories\Contracts\OrderBaseRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderBaseRepositoryEloquent::class,

            //订单信息表
            \Modules\Trade\Repositories\Contracts\OrderInfoRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderInfoRepositoryEloquent::class,

            //订单商品表
            \Modules\Trade\Repositories\Contracts\OrderItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderItemRepositoryEloquent::class,

            //订单数据表
            \Modules\Trade\Repositories\Contracts\OrderDataRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderDataRepositoryEloquent::class,

            //订单状态日志表
            \Modules\Trade\Repositories\Contracts\OrderStateLogRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderStateLogRepositoryEloquent::class,

            //订单收货地址表
            \Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderDeliveryAddressRepositoryEloquent::class,

            //订单物流表
            \Modules\Trade\Repositories\Contracts\OrderLogisticsRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderLogisticsRepositoryEloquent::class,

            //退单表
            \Modules\Trade\Repositories\Contracts\OrderReturnRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnRepositoryEloquent::class,

            //退单商品表
            \Modules\Trade\Repositories\Contracts\OrderReturnItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnItemRepositoryEloquent::class,

            //退款原因
            \Modules\Trade\Repositories\Contracts\OrderReturnReasonRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnReasonRepositoryEloquent::class,

            //订单发票
            \Modules\Trade\Repositories\Contracts\OrderInvoiceRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderInvoiceRepositoryEloquent::class,

            //购物车
            \Modules\Trade\Repositories\Contracts\UserCartRepository::class =>
                \Modules\Trade\Repositories\Eloquent\UserCartRepositoryEloquent::class,

            //订单评论
            \Modules\Trade\Repositories\Contracts\OrderCommentRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderCommentRepositoryEloquent::class,

            //推广订单
            \Modules\Trade\Repositories\Contracts\DistributionOrderRepository::class =>
                \Modules\Trade\Repositories\Eloquent\DistributionOrderRepositoryEloquent::class,

            //新购买订单
            \Modules\Trade\Repositories\Contracts\NewOrderRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderItemRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderAddressRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderAddressRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderTagRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderTagRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderRefundRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderRefundRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderLogRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderLogRepositoryEloquent::class,

            //新租赁订单
            \Modules\Trade\Repositories\Contracts\NewRentOrderRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderItemRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderAddressRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderAddressRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderTagRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderTagRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderRefundRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderRefundRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderLogRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderLogRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderOpRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderOpRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewRentOrderRepairRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewRentOrderRepairRepositoryEloquent::class,
            \Modules\Trade\Repositories\Contracts\NewOrderStockLockRepository::class =>
                \Modules\Trade\Repositories\Eloquent\NewOrderStockLockRepositoryEloquent::class,
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
