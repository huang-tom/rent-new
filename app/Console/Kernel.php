<?php

namespace App\Console;

use App\Console\Commands\UpdateActivityStatusCommand;
use App\Console\Commands\UpdateOrderStatusCommand;
use App\Console\Commands\UpdateProductStatusCommand;
use App\Console\Commands\UpdateVoucherStatusCommand;
use App\Console\Commands\SyncNewProductStockCommand;
use App\Console\Commands\RefreshNewProductStockCommand;
use App\Console\Commands\ReleaseNewOrderStockLockCommand;
use App\Console\Commands\CloseUnpaidNewOrderCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UpdateProductStatusCommand::class,
        UpdateActivityStatusCommand::class,
        UpdateOrderStatusCommand::class,
        UpdateVoucherStatusCommand::class,
        SyncNewProductStockCommand::class,
        RefreshNewProductStockCommand::class,
        ReleaseNewOrderStockLockCommand::class,
        CloseUnpaidNewOrderCommand::class,
    ];


    /**
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 计划任务 每分钟执行一次
        $schedule->command('order:update-status')->everyMinute();
        $schedule->command('voucher:update-status')->everyMinute();
        $schedule->command('new-order:release-stock-lock')->everyMinute();
        $schedule->command('new-order:close-unpaid')->everyMinute();

        // 新商品：每 2 分钟补齐已上架商品缺失的 Redis 库存
        $schedule->command('new-product:sync-stock')->everyTwoMinutes();

        // 计划任务 - 每天指定时间点执行
        $schedule->command('activity:update-status')->dailyAt('00:00');
        $schedule->command('product:update-status')->dailyAt('00:00');
        // 新商品：每天凌晨 3 点全量重写已上架商品 Redis 库存
        $schedule->command('new-product:refresh-stock')->dailyAt('03:00');

    }

}
