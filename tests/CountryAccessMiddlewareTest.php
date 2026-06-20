<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class CountryAccessMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        // Flush GeoService cache so each test starts fresh
        Cache::flush();

        Route::middleware('country.access')->post('/test-country', function () {
            return response('OK');
        });
    }

    private function fakeGeoResponse(string $countryCode): void {
        Http::fake([
            'http://ip-api.com/*' => Http::response(['countryCode' => $countryCode], 200),
        ]);
    }

    public function test_blocks_ip_from_non_allowed_country(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'allow',
            'defender.advanced_detection.country_access.countries' => ['ES'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('DE');

        $response = $this->post('/test-country');

        $response->assertStatus(429);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_allows_ip_from_allowed_country(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'allow',
            'defender.advanced_detection.country_access.countries' => ['ES'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('ES');

        $response = $this->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_deny_mode_blocks_listed_country(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'deny',
            'defender.advanced_detection.country_access.countries' => ['RU'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('RU');

        $response = $this->post('/test-country');

        $response->assertStatus(429);
    }

    public function test_deny_mode_allows_non_listed_country(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'deny',
            'defender.advanced_detection.country_access.countries' => ['RU'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('ES');

        $response = $this->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_whitelisted_ip_bypasses_country_check(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'allow',
            'defender.advanced_detection.country_access.countries' => ['ES'],
            'defender.advanced_detection.country_access.whitelist_ips' => ['127.0.0.1'],
        ]);

        // No Http::fake needed — whitelist check runs before GeoService is called
        $response = $this->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_passes_when_geo_service_unavailable(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'allow',
            'defender.advanced_detection.country_access.countries' => ['ES'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        // Simulate GeoService returning null (API down / connection error)
        Http::fake([
            'http://ip-api.com/*' => Http::response([], 500),
        ]);

        $response = $this->post('/test-country');

        // If country cannot be determined, request is allowed through
        $response->assertStatus(200);
    }

    public function test_blocked_country_fires_ip_blocked_event(): void {
        config([
            'defender.advanced_detection.country_access.mode' => 'allow',
            'defender.advanced_detection.country_access.countries' => ['ES'],
            'defender.advanced_detection.country_access.whitelist_ips' => [],
        ]);

        \Illuminate\Support\Facades\Event::fake([
            \Metalinked\LaravelDefender\Events\IpBlocked::class,
        ]);

        $this->fakeGeoResponse('CN');

        $this->post('/test-country');

        \Illuminate\Support\Facades\Event::assertDispatched(
            \Metalinked\LaravelDefender\Events\IpBlocked::class,
            fn ($e) => $e->ip === '127.0.0.1'
        );
    }
}
