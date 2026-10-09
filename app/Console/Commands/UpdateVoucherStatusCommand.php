<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Shop\Services\UserVoucherService;

class UpdateVoucherStatusCommand extends Command
{
    protected $signature = 'voucher:update-status';  // Artisan 命令
    protected $description = '用户优惠券状态更新';

    public function handle()
    {
        app(UserVoucherService::class)->updateVoucherState(); // 更新优惠券状态
        Log::info('更新优惠券状态计划任务执行成功');
    }

}
