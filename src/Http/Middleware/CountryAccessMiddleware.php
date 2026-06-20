<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Detection\GeoService;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Services\AlertManager;

class CountryAccessMiddleware {
    public function handle(Request $request, Closure $next) {
        $config = config('defender.advanced_detection.country_access', []);
        $ip = $request->ip();
        $countryCode = GeoService::getCountryCode($ip);

        $allowedCountries = $config['countries'] ?? [];
        $mode = $config['mode'] ?? 'allow';
        $whitelistIps = $config['whitelist_ips'] ?? [];

        if (in_array($ip, $whitelistIps) || ! $countryCode) {
            return $next($request);
        }

        if ($mode === 'allow' && ! in_array($countryCode, $allowedCountries)) {
            $reason = __('defender::defender.alert_non_allowed_country', ['country' => $countryCode]);

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

        if ($mode === 'deny' && in_array($countryCode, $allowedCountries)) {
            $reason = __('defender::defender.alert_denied_country', ['country' => $countryCode]);

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
