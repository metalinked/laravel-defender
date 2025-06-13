<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;
use Carbon\Carbon;
use Metalinked\LaravelDefender\Services\AlertManager;

class BruteForceMiddleware {
    public function handle(Request $request, Closure $next) {
        $ip = $request->ip();
        $maxAttempts = config('defender.brute_force.max_attempts', 5);
        $decayMinutes = config('defender.brute_force.decay_minutes', 10);

        $recentAttempts = IpLog::where('ip', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes($decayMinutes))
            ->count();

        if (($recentAttempts + 1) >= $maxAttempts) {
            AlertManager::send(
                __('defender::defender.alert_subject'),
                __('defender::defender.alert_too_many_attempts'),
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                ]
            );
            return response(
                __('defender::defender.alert_too_many_attempts'),
                429
            );
        }

        return $next($request);
    }
}
