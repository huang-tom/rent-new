<?php

namespace App\Middleware;

use Closure;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Repositories\Models\LogAction;

class LogsMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = User::getUser();

        // 构建日志记录
        $logData = [
            'user_id' => $user->user_id ?? 0,
            'user_account' => $user->user_account ?? '',
            'user_name' => $user->user_nickname ?? '',
            'log_name' => '',
            'action_id' => 0, // 根据需求定义具体 action_id
            'action_type_id' => 0, // 根据需求定义具体 action_type_id
            'log_url' => $request->path(),
            'log_method' => $request->method(),
            'log_param' => $request->all(),
            'log_ip' => $request->ip(),
            'log_date' => getCurDate(),
            'log_time' => getTime(),
        ];

        // 保存日志
        LogAction::create($logData);

        return $next($request);
    }
}
