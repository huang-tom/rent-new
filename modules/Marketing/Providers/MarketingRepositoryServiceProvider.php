<?php

namespace Modules\Marketing\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class MarketingRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [

            //活动
            \Modules\Marketing\Repositories\Contracts\ActivityBaseRepository::class =>
                \Modules\Marketing\Repositories\Eloquent\ActivityBaseRepositoryEloquent::class,

            //活动Item
            \Modules\Marketing\Repositories\Contracts\ActivityItemRepository::class =>
                \Modules\Marketing\Repositories\Eloquent\ActivityItemRepositoryEloquent::class,

            //拼团团数据
            \Modules\Marketing\Repositories\Contracts\ActivityGroupBookingRepository::class =>
                \Modules\Marketing\Repositories\Eloquent\ActivityGroupBookingRepositoryEloquent::class,

            //拼团历史数据
            \Modules\Marketing\Repositories\Contracts\ActivityGroupBookingHistoryRepository::class =>
                \Modules\Marketing\Repositories\Eloquent\ActivityGroupBookingHistoryRepositoryEloquent::class,

            //活动类型表
            \Modules\Marketing\Repositories\Contracts\ActivityTypeRepository::class =>
                \Modules\Marketing\Repositories\Eloquent\ActivityTypeRepositoryEloquent::class,

        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
