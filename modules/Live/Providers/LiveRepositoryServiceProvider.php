<?php

namespace Modules\Live\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class LiveRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        //主播申请管理
        $this->app->bind(\Modules\Live\Repositories\Contracts\UserApplyRepository::class,
            \Modules\Live\Repositories\Eloquent\UserApplyRepositoryEloquent::class);
    }
}
