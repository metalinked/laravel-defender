<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;

class IpLoggerMiddleware {
    public function handle(Request $request, Closure $next) {
        // Only log IP, route, method, user_id (if exists)
        IpLog::create([
            'ip' => $request->ip(),
            'route' => $request->path(),
            'method' => $request->method(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'is_suspicious' => false,
            'reason' => null,
        ]);

        return $next($request);
    }
}
