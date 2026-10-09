<?php

namespace Modules\Shop\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class ShopRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //物流公司
            \Modules\Shop\Repositories\Contracts\StoreExpressLogisticsRepository::class =>
                \Modules\Shop\Repositories\Eloquent\StoreExpressLogisticsRepositoryEloquent::class,

            //发货地址
            \Modules\Shop\Repositories\Contracts\StoreShippingAddressRepository::class =>
                \Modules\Shop\Repositories\Eloquent\StoreShippingAddressRepositoryEloquent::class,

            //物流工具
            \Modules\Shop\Repositories\Contracts\StoreTransportTypeRepository::class =>
                \Modules\Shop\Repositories\Eloquent\StoreTransportTypeRepositoryEloquent::class,

            //物流模板Item
            \Modules\Shop\Repositories\Contracts\StoreTransportItemRepository::class =>
                \Modules\Shop\Repositories\Eloquent\StoreTransportItemRepositoryEloquent::class,

            //商品收藏
            \Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository::class =>
                \Modules\Shop\Repositories\Eloquent\UserFavoritesItemRepositoryEloquent::class,

            //我的优惠券
            \Modules\Shop\Repositories\Contracts\UserVoucherRepository::class =>
                \Modules\Shop\Repositories\Eloquent\UserVoucherRepositoryEloquent::class,

            //优惠券数量记录
            \Modules\Shop\Repositories\Contracts\UserVoucherNumRepository::class =>
                \Modules\Shop\Repositories\Eloquent\UserVoucherNumRepositoryEloquent::class,

            //用户搜索记录
            \Modules\Shop\Repositories\Contracts\UserSearchHistoryRepository::class =>
                \Modules\Shop\Repositories\Eloquent\UserSearchHistoryRepositoryEloquent::class,
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
