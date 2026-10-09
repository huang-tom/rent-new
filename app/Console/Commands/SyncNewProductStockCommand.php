<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Pt\Services\NewProductService;

class SyncNewProductStockCommand extends Command
{
    protected $signature = 'new-product:sync-stock';
    protected $description = '补齐已上架新商品的 Redis 库存缓存';

    public function handle()
    {
        $result = app(NewProductService::class)->syncOnShelfStockCache();

        $msg = sprintf(
            '新商品库存缓存补齐完成：上架%d，缺失%d，已补%d',
            $result['total'],
            $result['missing'],
            $result['fixed']
        );
        $this->info($msg);
        Log::info($msg);
    }
}
