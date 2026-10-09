<?php

namespace Modules\Account\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class AccountRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            \Modules\Account\Repositories\Contracts\UserRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserInfoRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserInfoRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserLevelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserLevelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserTagGroupRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserTagGroupRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserTagBaseRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserTagBaseRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserMessageRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserMessageRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserDeliveryAddressRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserInvoiceRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserInvoiceRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserBindConnectRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserBindConnectRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserFriendRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserFriendRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserGroupRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserGroupRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserGroupRelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserGroupRelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserZoneRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserZoneRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserZoneRelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserZoneRelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserDistributionRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserDistributionRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserLoginRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserLoginRepositoryEloquent::class,
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
