<?php

namespace Modules\O2o\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class O2oRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //门店
            \Modules\O2o\Repositories\Contracts\ChainBaseRepository::class =>
                \Modules\O2o\Repositories\Eloquent\ChainBaseRepositoryEloquent::class,

            //门店商品
            \Modules\O2o\Repositories\Contracts\ChainItemRepository::class =>
                \Modules\O2o\Repositories\Eloquent\ChainItemRepositoryEloquent::class,

            //门店分类
            \Modules\O2o\Repositories\Contracts\ChainCategoryRepository::class =>
                \Modules\O2o\Repositories\Eloquent\ChainCategoryRepositoryEloquent::class,

            //门店用户
            \Modules\O2o\Repositories\Contracts\ChainUserRepository::class =>
                \Modules\O2o\Repositories\Eloquent\ChainUserRepositoryEloquent::class,

        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
