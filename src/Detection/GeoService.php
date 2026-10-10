<?php

namespace Metalinked\LaravelDefender\Detection;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoService {
    /**
     * Get country code from IP address using the configured geolocation provider.
     *
     * @param string $ip The IP address to check
     * @param string|null $provider Optional provider override (ip-api, ipinfo, ipgeolocation)
     * @return string|null Country code or null if not available
     */
    public static function getCountryCode(string $ip, ?string $provider = null): ?string {
        // Private/reserved addresses (localhost, LAN, docker) have no country: don't ask anyone.
        if (! self::isPublicIp($ip)) {
            return null;
        }

        $provider = strtolower($provider ?? config('defender.geo.provider', 'ip-api'));
        $cacheKey = "defender_geo_{$provider}_{$ip}";

        // '' marks "looked up, no answer", so a failing provider isn't hit on every request.
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached === '' ? null : $cached;
        }

        $countryCode = match ($provider) {
            'ipinfo' => self::getCountryCodeFromIpInfo($ip),
            'ipgeolocation' => self::getCountryCodeFromIpGeolocation($ip),
            default => self::getCountryCodeFromIpApi($ip), // 'ip-api' (and the old 'ipapi' alias)
        };

        $minutes = (int) config('defender.geo.cache_minutes', 60);
        Cache::put($cacheKey, $countryCode ?: '', now()->addMinutes($countryCode ? $minutes : min($minutes, 5)));

        return $countryCode ?: null;
    }

    public static function isPublicIp(string $ip): bool {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Get country code using ip-api.com (free tier)
     */
    protected static function getCountryCodeFromIpApi(string $ip): ?string {
        try {
            $geo = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=countryCode")->json();

            return $geo['countryCode'] ?? null;
        } catch (\Exception $e) {
            Log::debug("Laravel Defender: ip-api.com geo lookup failed: " . $e->getMessage());

            return null;
        }
    }
    
    /**
     * Get country code using ipinfo.io (requires API key for production use)
     */
    protected static function getCountryCodeFromIpInfo(string $ip): ?string {
        $token = config('defender.geo.ipinfo_token');
        
        try {
            $url = "https://ipinfo.io/{$ip}/json";
            $headers = $token ? ['Authorization' => "Bearer {$token}"] : [];
            
            $geo = Http::timeout(3)->withHeaders($headers)->get($url)->json();

            return $geo['country'] ?? null;
        } catch (\Exception $e) {
            Log::debug("Laravel Defender: ipinfo.io geo lookup failed: " . $e->getMessage());

            return null;
        }
    }
    
    /**
     * Get country code using ipgeolocation.io (requires API key)
     */
    protected static function getCountryCodeFromIpGeolocation(string $ip): ?string {
        $apiKey = config('defender.geo.ipgeolocation_key');
        
        if (! $apiKey) {
            return null;
        }
        
        try {
            $url = "https://api.ipgeolocation.io/ipgeo?apiKey={$apiKey}&ip={$ip}&fields=country_code2";
            $geo = Http::timeout(3)->get($url)->json();

            return $geo['country_code2'] ?? null;
        } catch (\Exception $e) {
            Log::debug("Laravel Defender: ipgeolocation.io lookup failed: " . $e->getMessage());

            return null;
        }
    }
    
    /**
     * Try all available providers in sequence until a valid country code is found
     * Useful as a fallback mechanism
     */
    public static function getCountryCodeWithFallback(string $ip): ?string {
        // Try all providers in sequence
        foreach (['ip-api', 'ipinfo', 'ipgeolocation'] as $provider) {
            $countryCode = self::getCountryCode($ip, $provider);
            if ($countryCode) {
                return $countryCode;
            }
        }
        
        return null;
    }
}
