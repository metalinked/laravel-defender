<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Services\AlertManager;

class AdvancedDetectionMiddleware {
    public function handle(Request $request, Closure $next) {
        $advConfig = config('defender.advanced_detection', []);
        $ip = $request->ip();
        $isSuspicious = false;
        $reason = null;

        // Suspicious user-agent detection
        if (!empty($advConfig['suspicious_user_agents'])) {
            $userAgent = strtolower($request->header('User-Agent', ''));
            foreach ($advConfig['suspicious_user_agents'] as $pattern) {
                if (str_contains($userAgent, $pattern)) {
                    $isSuspicious = true;
                    $reason = __('defender::defender.alert_suspicious_user_agent', ['user_agent' => $userAgent]);
                    break;
                }
            }
        }

        // Suspicious routes detection
        if (!$isSuspicious && !empty($advConfig['suspicious_routes'])) {
            $path = '/' . ltrim($request->path(), '/');
            foreach ($advConfig['suspicious_routes'] as $route) {
                if (str_starts_with($path, $route)) {
                    $isSuspicious = true;
                    $reason = __('defender::defender.alert_suspicious_route', ['route' => $path]);
                    break;
                }
            }
        }

        // Common username detection (for login routes)
        if (
            !$isSuspicious &&
            !empty($advConfig['common_usernames']) &&
            $request->is('login') &&
            in_array(strtolower($request->input('username', '')), $advConfig['common_usernames'])
        ) {
            $isSuspicious = true;
            $reason = __('defender::defender.alert_common_username', ['username' => $request->input('username')]);
        }

        // If suspicious, block the request and alert
        if ($isSuspicious) {
            AlertManager::send(
                __('defender::defender.alert_subject'),
                $reason ?? __('defender::defender.access_blocked'),
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                    'request' => $request,
                    'reason' => $reason ?? __('defender::defender.access_blocked'),
                ]
            );
            return response($reason ?? __('defender::defender.access_blocked'), 429);
        }

        return $next($request);
    }
}
