<?php

namespace App\Middleware;

use Closure;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $menuPath = $request->path();

        // 使用 Gate 校验权限
        if (Gate::denies('access-menu', $menuPath)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

}
