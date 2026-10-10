<?php

namespace Metalinked\LaravelDefender\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Metalinked\LaravelDefender\Defender;
use Metalinked\LaravelDefender\Detection\GeoService;
use Symfony\Component\HttpFoundation\IpUtils;

class CountryAccessMiddleware {
    public function handle(Request $request, Closure $next) {
        $config = config('defender.country_access', []);
        $countries = array_map('strtoupper', $config['countries'] ?? []);

        // An empty list means the feature is off, so no geo lookup at all.
        if (! $countries || Defender::isWhitelisted($request)) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        // Pre-3.0 per-feature whitelist, still honoured.
        $legacyWhitelist = $config['whitelist_ips'] ?? [];
        if ($legacyWhitelist && IpUtils::checkIp($ip, $legacyWhitelist)) {
            return $next($request);
        }

        // Unknown country (private IP, provider down): let it through rather than lock everyone out.
        $country = GeoService::getCountryCode($ip);
        if (! $country) {
            return $next($request);
        }

        $listed = in_array(strtoupper($country), $countries, true);
        $reason = match (true) {
            ($config['mode'] ?? 'allow') === 'deny' && $listed => __('defender::defender.alert_denied_country', ['country' => $country]),
            ($config['mode'] ?? 'allow') !== 'deny' && ! $listed => __('defender::defender.alert_non_allowed_country', ['country' => $country]),
            default => null,
        };

        if ($reason && $response = Defender::block($request, $reason, 403)) {
            return $response;
        }

        return $next($request);
    }
}
