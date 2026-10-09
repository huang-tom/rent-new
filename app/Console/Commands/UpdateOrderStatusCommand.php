<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Trade\Services\OrderInfoService;

class UpdateOrderStatusCommand extends Command
{
    protected $signature = 'order:update-status';  // Artisan 命令
    protected $description = '订单状态计划任务';

    public function handle()
    {
        $orderInfoService = app(OrderInfoService::class);
        $orderInfoService->autoCancelOrder(); // 取消订单
        $orderInfoService->autoReceive(); // 订单自动确认收货

        Log::info('订单状态计划任务执行成功');
    }

}
