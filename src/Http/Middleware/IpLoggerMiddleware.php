<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;

class IpLoggerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $log = [
            'ip' => $request->ip(),
            'route' => $request->route() ? $request->route()->getName() : $request->path(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
            'is_suspicious' => false,
            'reason' => null,
        ];

        // Here you can add detection of suspicious patterns (e.g., too many attempts)
        // Example: $log['is_suspicious'] = ...; $log['reason'] = ...;

        IpLog::create($log);

        return $next($request);
    }
}