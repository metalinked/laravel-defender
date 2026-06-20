<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockedIpMiddleware {
    public function handle(Request $request, Closure $next) {
        if (BlocklistService::isBlocked($request->ip())) {
            return response(__('defender::defender.ip_blocked'), 403);
        }

        return $next($request);
    }
}
