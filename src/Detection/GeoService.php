<?php

namespace Metalinked\LaravelDefender\Detection;

use Illuminate\Support\Facades\Http;

class GeoService {
    public static function getCountryCode(string $ip): ?string {
        try {
            $geo = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=countryCode")->json();
            return $geo['countryCode'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
