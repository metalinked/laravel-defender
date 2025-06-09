<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Alert\AlertManager;
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

        // Advanced detection config
        $advConfig = config('defender.advanced_detection', []);
        $advancedDetectionEnabled = $advConfig['enabled'] ?? false;
        $isSuspicious = false;
        $reason = null;

        // --- COUNTRY ACCESS CONTROL ---
        $countryConfig = $advConfig['country_access'] ?? [];
        $allowedCountries = $countryConfig['countries'] ?? [];
        $mode = $countryConfig['mode'] ?? 'allow';
        $whitelistIps = $countryConfig['whitelist_ips'] ?? [];
        $clientIp = $ip;

        // 1. Whitelist IP: always allow
        if (!in_array($clientIp, $whitelistIps)) {
            // 2. Geolocate the IP (with ip-api.com)
            $countryCode = null;
            try {
                $geo = Http::timeout(2)->get("http://ip-api.com/json/{$clientIp}?fields=countryCode")->json();
                $countryCode = $geo['countryCode'] ?? null;
            } catch (\Exception $e) {}

            if ($countryCode) {
                if ($mode === 'allow' && !in_array($countryCode, $allowedCountries)) {
                    $isSuspicious = true;
                    $reason = "Access from non-allowed country: $countryCode";
                }
                if ($mode === 'deny' && in_array($countryCode, $allowedCountries)) {
                    $isSuspicious = true;
                    $reason = "Access from denied country: $countryCode";
                }
            }
        }

        // Suspicious user-agent
        if (!$isSuspicious && $advancedDetectionEnabled && !empty($advConfig['suspicious_user_agents'])) {
            $userAgent = strtolower($request->header('User-Agent', ''));
            foreach ($advConfig['suspicious_user_agents'] as $pattern) {
                if (str_contains($userAgent, $pattern)) {
                    $isSuspicious = true;
                    $reason = "Suspicious user-agent: $userAgent";
                    break;
                }
            }
        }

        // Suspicious routes
        if (!$isSuspicious && $advancedDetectionEnabled && !empty($advConfig['suspicious_routes'])) {
            $path = '/' . ltrim($request->path(), '/');
            foreach ($advConfig['suspicious_routes'] as $route) {
                if (str_starts_with($path, $route)) {
                    $isSuspicious = true;
                    $reason = "Suspicious route accessed: $path";
                    break;
                }
            }
        }

        // Login with common username
        if (!$isSuspicious && $advancedDetectionEnabled && !empty($advConfig['common_usernames'])) {
            if ($request->is('login') && in_array(strtolower($request->input('username', '')), $advConfig['common_usernames'])) {
                $isSuspicious = true;
                $reason = "Login attempt with common username: " . $request->input('username');
            }
        }

        // Brute force detection
        $recentAttempts = IpLog::where('ip', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes($decayMinutes))
            ->count();

        $attemptsIncludingCurrent = $recentAttempts + 1;
        if (!$isSuspicious && $attemptsIncludingCurrent >= $maxAttempts) {
            $isSuspicious = true;
            $reason = "Too many login attempts";
        }

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

                // Alert system
                AlertManager::send('Suspicious IP detected', [
                    'ip' => $ip,
                    'route' => $routeName,
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                ]);
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

            // Alert system
            if ($isSuspicious) {
                AlertManager::send('Suspicious IP detected', [
                    'ip' => $ip,
                    'route' => $routeName,
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'reason' => $reason,
                ]);
            }

            // AbuseIPDB check (optional)
            if (!empty($config['alert_channels']) && in_array('abuseipdb', $config['alert_channels']) && !empty($config['abuseipdb_api_key'])) {
                $this->checkAbuseIpDb($ip, $config['abuseipdb_api_key']);
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
                // TODO: add actions based on the score?
            } else {
                \Log::warning("AbuseIPDB API error for $ip: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("AbuseIPDB API exception for $ip: " . $e->getMessage());
        }
    }
}
