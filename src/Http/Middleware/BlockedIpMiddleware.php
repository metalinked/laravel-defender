<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockedIpMiddleware {
    public function handle(Request $request, Closure $next) {
        if (Defender::isWhitelisted($request) || ! BlocklistService::isBlocked((string) $request->ip())) {
            return $next($request);
        }

        return Defender::block($request, __('defender::defender.alert_blocked_ip'), 403) ?? $next($request);
    }
}
