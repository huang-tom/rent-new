<?php

namespace Modules\Invoicing\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class InvoicingRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        //出入库
        $this->app->bind(\Modules\Invoicing\Repositories\Contracts\StockBillRepository::class,
            \Modules\Invoicing\Repositories\Eloquent\StockBillRepositoryEloquent::class);

        //出入库
        $this->app->bind(\Modules\Invoicing\Repositories\Contracts\StockBillItemRepository::class,
            \Modules\Invoicing\Repositories\Eloquent\StockBillItemRepositoryEloquent::class);

    }
}
