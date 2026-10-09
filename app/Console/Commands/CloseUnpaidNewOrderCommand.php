<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Trade\Services\NewOrderStockLockService;

class CloseUnpaidNewOrderCommand extends Command
{
    protected $signature = 'new-order:close-unpaid';
    protected $description = '关闭超过30分钟仍未支付的购买订单和租赁订单';

    public function handle()
    {
        $result = app(NewOrderStockLockService::class)->closeUnpaidOrders();
        $msg = sprintf('超时未支付关单完成：购买%d，租赁%d', $result['buy'], $result['rent']);
        $this->info($msg);
        Log::info($msg);
    }
}
