<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Services\AlertManager;

class IpLoggerMiddleware {
    
    public function handle(Request $request, Closure $next) {
        if (config('defender.ip_logging.log_all', false)) {
            AlertManager::send(
                'Access logged',
                'Access to route: ' . $request->path(),
                [
                    'request' => $request,
                    'reason' => null,
                    'is_suspicious' => false,
                ]
            );
        }

        return $next($request);
    }
}