<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Detection\GeoService;
use Metalinked\LaravelDefender\Services\AlertManager;

class CountryAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $config = config('defender.advanced_detection.country_access', []);
        $ip = $request->ip();
        $countryCode = GeoService::getCountryCode($ip);

        $allowedCountries = $config['countries'] ?? [];
        $mode = $config['mode'] ?? 'allow';
        $whitelistIps = $config['whitelist_ips'] ?? [];

        // Skip check for whitelisted IPs or if country code is not available
        if (in_array($ip, $whitelistIps) || ! $countryCode) {
            return $next($request);
        }

        if ($mode === 'allow' && ! in_array($countryCode, $allowedCountries)) {
            AlertManager::send(
                __('defender::defender.alert_subject'),
                __('defender::defender.alert_non_allowed_country', ['country' => $countryCode]),
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                    'request' => $request,
                    'reason' => __('defender::defender.alert_non_allowed_country', ['country' => $countryCode]),
                ]
            );

            return response(
                __('defender::defender.alert_non_allowed_country', ['country' => $countryCode]),
                429
            );
        }

        if ($mode === 'deny' && in_array($countryCode, $allowedCountries)) {
            AlertManager::send(
                __('defender::defender.alert_subject'),
                __('defender::defender.alert_denied_country', ['country' => $countryCode]),
                [
                    'ip' => $ip,
                    'route' => $request->path(),
                    'is_suspicious' => true,
                    'request' => $request,
                    'reason' => __('defender::defender.alert_denied_country', ['country' => $countryCode]),
                ]
            );

            return response(
                __('defender::defender.alert_denied_country', ['country' => $countryCode]),
                429
            );
        }

        return $next($request);
    }
}
