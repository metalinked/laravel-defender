<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Services\AlertManager;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockedIpMiddleware {
    public function handle(Request $request, Closure $next) {
        $ip = $request->ip();

        if (BlocklistService::isBlocked($ip)) {
            $reason = __('defender::defender.alert_blocked_ip');

            AlertManager::send(
                __('defender::defender.alert_subject'),
                $reason,
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                    'request' => $request,
                    'reason' => $reason,
                ]
            );

            event(new IpBlocked($ip, $reason, $request));

            return response(__('defender::defender.ip_blocked'), 403);
        }

        return $next($request);
    }
}
