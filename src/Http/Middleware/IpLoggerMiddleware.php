<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class IpLoggerMiddleware {
    public function handle(Request $request, Closure $next) {
        $config = config('defender.ip_logging');
        if (!($config['enabled'] ?? true)) {
            return $next($request);
        }

        $ip = $request->ip();
        $maxAttempts = $config['max_attempts'] ?? 5;
        $decayMinutes = $config['decay_minutes'] ?? 10;

        // Count recent attempts
        $recentAttempts = IpLog::where('ip', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes($decayMinutes))
            ->count();

        $attemptsIncludingCurrent = $recentAttempts + 1;
        $isSuspicious = $attemptsIncludingCurrent >= $maxAttempts;
        $reason = $isSuspicious ? "Too many login attempts" : null;

        // Block if configured and suspicious
        if ($isSuspicious && ($config['block_suspicious'] ?? false)) {
            if ($config['log_all'] || $isSuspicious) {
                $route = $request->route();
                $routeName = ($route && $route->getName()) ? $route->getName() : $request->path();

                $log = [
                    'ip' => $ip,
                    'route' => $routeName,
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'is_suspicious' => $isSuspicious,
                    'reason' => $reason,
                ];
                $ipLog = IpLog::create($log);

                // Alerts for configured channels
                if (!empty($config['alert_channels'])) {
                    foreach ($config['alert_channels'] as $channel) {
                        if ($channel === 'log') {
                            \Log::warning("Suspicious IP detected: $ip ($reason)");
                        }
                        if ($channel === 'mail') {
                            // TODO: Send alert email
                        }
                        if ($channel === 'slack') {
                            // TODO: Send Slack alert
                        }
                        if ($channel === 'webhook') {
                            // TODO: Send webhook alert
                        }
                        if ($channel === 'abuseipdb' && !empty($config['abuseipdb_api_key'])) {
                            $this->checkAbuseIpDb($ip, $config['abuseipdb_api_key']);
                        }
                    }
                }
            }
            return response('Access blocked due to suspicious activity.', 429);
        }

        // Only log if log_all is enabled or if it is suspicious
        if ($config['log_all'] || $isSuspicious) {
            $route = $request->route();
            $routeName = ($route && $route->getName()) ? $route->getName() : $request->path();

            $log = [
                'ip' => $ip,
                'route' => $routeName,
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'is_suspicious' => $isSuspicious,
                'reason' => $reason,
            ];

            $ipLog = IpLog::create($log);

            // Alerts for configured channels
            if ($isSuspicious && !empty($config['alert_channels'])) {
                foreach ($config['alert_channels'] as $channel) {
                    if ($channel === 'log') {
                        \Log::warning("Suspicious IP detected: $ip ($reason)");
                    }
                    if ($channel === 'mail') {
                        // TODO: Send alert email
                    }
                    if ($channel === 'slack') {
                        // TODO: Send Slack alert
                    }
                    if ($channel === 'webhook') {
                        // TODO: Send webhook alert
                    }
                    if ($channel === 'abuseipdb' && !empty($config['abuseipdb_api_key'])) {
                        $this->checkAbuseIpDb($ip, $config['abuseipdb_api_key']);
                    }
                }
            }
        }

        return $next($request);
    }

    protected function checkAbuseIpDb($ip, $apiKey) {
        try {
            $response = Http::withHeaders([
                'Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get('https://api.abuseipdb.com/api/v2/check', [
                'ipAddress' => $ip,
                'maxAgeInDays' => 90,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $score = $data['data']['abuseConfidenceScore'] ?? null;
                \Log::info("AbuseIPDB score for $ip: $score");
                // Pots afegir accions segons el score si vols
            } else {
                \Log::warning("AbuseIPDB API error for $ip: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("AbuseIPDB API exception for $ip: " . $e->getMessage());
        }
    }
}
