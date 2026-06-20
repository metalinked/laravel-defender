<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IpLoggerMiddleware {
    public function handle(Request $request, Closure $next) {
        if (config('defender.ip_logging.log_all', false)) {
            Log::info('[Defender] Request logged', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'route' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $next($request);
    }
}
