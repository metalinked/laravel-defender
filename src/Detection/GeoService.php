<?php

namespace Metalinked\LaravelDefender\Detection;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeoService {
    /**
     * Get country code from IP address using the configured geolocation provider.
     *
     * @param string $ip The IP address to check
     * @param string|null $provider Optional provider override (ip-api, ipinfo, ipgeolocation)
     * @return string|null Country code or null if not available
     */
    public static function getCountryCode(string $ip, ?string $provider = null): ?string {
        // Use default provider from config if not specified
        $provider = $provider ?? config('defender.advanced_detection.geo_provider', 'ip-api');
        
        // Normalize provider name
        $provider = strtolower($provider);
        
        // Cache key for storing country code
        $cacheKey = "defender_geo_{$ip}";
        
        // Check cache first (10 min cache by default)
        $cacheTtl = config('defender.advanced_detection.geo_cache_minutes', 10);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Attempt to retrieve from selected provider
        $countryCode = match($provider) {
            'ip-api' => self::getCountryCodeFromIpApi($ip),
            'ipinfo' => self::getCountryCodeFromIpInfo($ip),
            'ipgeolocation' => self::getCountryCodeFromIpGeolocation($ip),
            'ipapi' => self::getCountryCodeFromIpApi($ip), // Alias for backward compatibility
            default => self::getCountryCodeFromIpApi($ip)
        };
        
        // If we got a country code, cache it
        if ($countryCode) {
            Cache::put($cacheKey, $countryCode, now()->addMinutes($cacheTtl));
        }
        
        return $countryCode;
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
        $token = config('defender.advanced_detection.ipinfo_token');
        
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
        $apiKey = config('defender.advanced_detection.ipgeolocation_key');
        
        if (!$apiKey) {
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