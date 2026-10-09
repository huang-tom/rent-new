<?php

namespace App\Middleware;

use Closure;

class CrossMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $origin = $request->header('Origin');

        // [安全修复] 不再无条件反射任意 Origin。仅当 Origin 与请求主机同源时才放行 CORS，
        // 防止第三方网站携带用户凭证跨域调用 API（同源部署本不需要 CORS）。
        // CORS_EXTRA_ORIGINS：额外信任的主机名列表（逗号分隔，如本机调试 localhost/127.0.0.1）
        if ($origin) {
            $originHost = parse_url($origin, PHP_URL_HOST);
            $requestHost = $request->getHost();
            $extra = array_filter(array_map('trim', explode(',', (string) env('CORS_EXTRA_ORIGINS', ''))));
            if ($originHost && (strcasecmp($originHost, $requestHost) === 0 || in_array($originHost, $extra, true))) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header("Access-Control-Allow-Credentials: true");
                header("Access-Control-Allow-Methods: *");
                header("Access-Control-Allow-Headers: Content-Type,Token,Access-Token,Authorization");
                header("Access-Control-Expose-Headers: *");
            }
        }

        // [修复] 接口响应禁止浏览器缓存，保证站点配置/菜单等即时生效
        header('Cache-Control: no-store, no-cache, must-revalidate');

        if ($request->isMethod('OPTIONS')) {
            return response('', 200);
        }

        $url = $request->getUri();
        if(str_contains($url,'image.php'))
        {
            $image =  app('Modules\Sys\Http\Controllers\ImageController');
            return $image->get($request);
        }

        return $next($request);
    }
}
