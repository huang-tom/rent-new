<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Marketing\Services\ActivityBaseService;

class UpdateActivityStatusCommand extends Command
{
    protected $signature = 'activity:update-status';  // Artisan 命令
    protected $description = '更新活动状态';

    public function handle()
    {
        app(ActivityBaseService::class)->updateActivityState(); // 更新活动状态
        Log::info('更新活动状态计划任务执行成功');
    }

}
