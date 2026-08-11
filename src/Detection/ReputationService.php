<?php

namespace Metalinked\LaravelDefender\Detection;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReputationService {
    /**
     * Get the abuse confidence score (0-100) for an IP address from the
     * configured reputation provider. Returns null if reputation lookups
     * are disabled, no API key is configured, or the lookup fails.
     */
    public static function getScore(string $ip): ?int {
        if (! config('defender.reputation.enabled', false)) {
            return null;
        }

        $cacheKey = "defender_reputation_{$ip}";

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $provider = strtolower(config('defender.reputation.provider', 'abuseipdb'));

        $score = match ($provider) {
            'abuseipdb' => self::getScoreFromAbuseIpDb($ip),
            default => null,
        };

        if ($score !== null) {
            $cacheMinutes = (int) config('defender.reputation.cache_minutes', 60);
            Cache::put($cacheKey, $score, now()->addMinutes($cacheMinutes));
        }

        return $score;
    }

    /**
     * Get the abuse confidence score using AbuseIPDB's /check endpoint.
     */
    protected static function getScoreFromAbuseIpDb(string $ip): ?int {
        $apiKey = config('defender.reputation.api_key');

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'Key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get('https://api.abuseipdb.com/api/v2/check', [
                    'ipAddress' => $ip,
                    'maxAgeInDays' => 90,
                ])
                ->json();

            return $response['data']['abuseConfidenceScore'] ?? null;
        } catch (\Exception $e) {
            Log::debug('Laravel Defender: AbuseIPDB reputation lookup failed: ' . $e->getMessage());

            return null;
        }
    }
}
