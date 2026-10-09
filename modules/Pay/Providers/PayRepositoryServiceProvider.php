<?php

namespace Modules\Pay\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class PayRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        //用户资产表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\UserResourceRepository::class,
            \Modules\Pay\Repositories\Eloquent\UserResourceRepositoryEloquent::class);

        //流水表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\ConsumeRecordRepository::class,
            \Modules\Pay\Repositories\Eloquent\ConsumeRecordRepositoryEloquent::class);

        //交易表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\ConsumeTradeRepository::class,
            \Modules\Pay\Repositories\Eloquent\ConsumeTradeRepositoryEloquent::class);

        //充值表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\ConsumeDepositRepository::class,
            \Modules\Pay\Repositories\Eloquent\ConsumeDepositRepositoryEloquent::class);

        //积分记录
        $this->app->bind(\Modules\Pay\Repositories\Contracts\UserPointsHistoryRepository::class,
            \Modules\Pay\Repositories\Eloquent\UserPointsHistoryRepositoryEloquent::class);

        //经验记录
        $this->app->bind(\Modules\Pay\Repositories\Contracts\UserExpHistoryRepository::class,
            \Modules\Pay\Repositories\Eloquent\UserExpHistoryRepositoryEloquent::class);

        //支付密码
        $this->app->bind(\Modules\Pay\Repositories\Contracts\UserPayRepository::class,
            \Modules\Pay\Repositories\Eloquent\UserPayRepositoryEloquent::class);

        //用户银行列表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\UserBankCardRepository::class,
            \Modules\Pay\Repositories\Eloquent\UserBankCardRepositoryEloquent::class);

        //银行列表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\BaseBankRepository::class,
            \Modules\Pay\Repositories\Eloquent\BaseBankRepositoryEloquent::class);

        //推广员佣金表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\DistributionCommissionRepository::class,
            \Modules\Pay\Repositories\Eloquent\DistributionCommissionRepositoryEloquent::class);

        //提现列表
        $this->app->bind(\Modules\Pay\Repositories\Contracts\ConsumeWithdrawRepository::class,
            \Modules\Pay\Repositories\Eloquent\ConsumeWithdrawRepositoryEloquent::class);
    }
}
