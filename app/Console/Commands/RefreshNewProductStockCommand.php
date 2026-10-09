<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Pt\Services\NewProductService;

class RefreshNewProductStockCommand extends Command
{
    protected $signature = 'new-product:refresh-stock';
    protected $description = '全量刷新已上架新商品的 Redis 库存缓存（以库表为准）';

    public function handle()
    {
        $result = app(NewProductService::class)->refreshOnShelfStockCache();

        $msg = sprintf(
            '新商品库存缓存全量刷新完成：上架%d，已重写%d',
            $result['total'],
            $result['fixed']
        );
        $this->info($msg);
        Log::info($msg);
    }
}
