<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Services\AlertManager;

class BruteForceMiddleware {
    public function handle(Request $request, Closure $next) {
        $ip = $request->ip();
        $maxAttempts = config('defender.brute_force.max_attempts', 5);
        $decayMinutes = config('defender.brute_force.decay_minutes', 10);

        $cacheKey = "defender:brute_count:{$ip}";

        $recentAttempts = Cache::remember($cacheKey, 30, function () use ($ip, $decayMinutes) {
            return IpLog::where('ip', $ip)
                ->where('is_suspicious', true)
                ->where('created_at', '>=', Carbon::now()->subMinutes($decayMinutes))
                ->count();
        });

        if (($recentAttempts + 1) >= $maxAttempts) {
            Cache::forget($cacheKey);

            $reason = __('defender::defender.alert_too_many_attempts');

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

            return response($reason, 429);
        }

        return $next($request);
    }
}
