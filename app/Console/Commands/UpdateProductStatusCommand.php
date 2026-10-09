<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Pt\Services\ProductIndexService;

class UpdateProductStatusCommand extends Command
{
    protected $signature = 'product:update-status';  // Artisan 命令
    protected $description = '商品上架计划任务';

    public function handle()
    {
        app(ProductIndexService::class)->autoSaleProduct();

        Log::info('商品定时上架计划任务执行成功');
    }

}
