<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Trade\Services\NewOrderStockLockService;

class ReleaseNewOrderStockLockCommand extends Command
{
    protected $signature = 'new-order:release-stock-lock';
    protected $description = '处理购买/租赁订单库存占用：超时5分钟未支付则释放，已支付则标记已处理';

    public function handle()
    {
        $result = app(NewOrderStockLockService::class)->releaseExpiredLocks();
        $msg = sprintf('库存占用处理完成：已支付%d单，释放%d单', $result['paid'], $result['released']);
        $this->info($msg);
        Log::info($msg);
    }
}
