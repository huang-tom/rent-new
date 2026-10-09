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
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
