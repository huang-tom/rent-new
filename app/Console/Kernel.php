<?php

namespace App\Console;

use App\Console\Commands\UpdateActivityStatusCommand;
use App\Console\Commands\UpdateOrderStatusCommand;
use App\Console\Commands\UpdateProductStatusCommand;
use App\Console\Commands\UpdateVoucherStatusCommand;
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
        UpdateVoucherStatusCommand::class
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

        // 计划任务 - 每天指定时间点执行
        $schedule->command('activity:update-status')->dailyAt('00:00');
        $schedule->command('product:update-status')->dailyAt('00:00');

    }

}
