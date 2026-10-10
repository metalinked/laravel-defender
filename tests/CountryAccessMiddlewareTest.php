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

        Route::middleware('defender.country_access')->post('/test-country', function () {
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
            'defender.country_access.mode' => 'allow',
            'defender.country_access.countries' => ['ES'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('DE');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        $response->assertStatus(403);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_allows_ip_from_allowed_country(): void {
        config([
            'defender.country_access.mode' => 'allow',
            'defender.country_access.countries' => ['ES'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('ES');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_deny_mode_blocks_listed_country(): void {
        config([
            'defender.country_access.mode' => 'deny',
            'defender.country_access.countries' => ['RU'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('RU');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        $response->assertStatus(403);
    }

    public function test_deny_mode_allows_non_listed_country(): void {
        config([
            'defender.country_access.mode' => 'deny',
            'defender.country_access.countries' => ['RU'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        $this->fakeGeoResponse('ES');

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_whitelisted_ip_bypasses_country_check(): void {
        config([
            'defender.country_access.mode' => 'allow',
            'defender.country_access.countries' => ['ES'],
            'defender.country_access.whitelist_ips' => ['8.8.8.8'],
        ]);

        // Pre-3.0 per-feature whitelist is still honoured
        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        $response->assertStatus(200);
    }

    public function test_passes_when_geo_service_unavailable(): void {
        config([
            'defender.country_access.mode' => 'allow',
            'defender.country_access.countries' => ['ES'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        // Simulate GeoService returning null (API down / connection error)
        Http::fake([
            'http://ip-api.com/*' => Http::response([], 500),
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        // If country cannot be determined, request is allowed through
        $response->assertStatus(200);
    }

    public function test_blocked_country_fires_ip_blocked_event(): void {
        config([
            'defender.country_access.mode' => 'allow',
            'defender.country_access.countries' => ['ES'],
            'defender.country_access.whitelist_ips' => [],
        ]);

        \Illuminate\Support\Facades\Event::fake([
            \Metalinked\LaravelDefender\Events\IpBlocked::class,
        ]);

        $this->fakeGeoResponse('CN');

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country');

        \Illuminate\Support\Facades\Event::assertDispatched(
            \Metalinked\LaravelDefender\Events\IpBlocked::class,
            fn ($e) => $e->ip === '8.8.8.8'
        );
    }

    public function test_empty_country_list_disables_the_check_without_geo_lookup(): void {
        config(['defender.country_access.countries' => []]);
        Http::fake();

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country')->assertOk();

        Http::assertNothingSent();
    }

    public function test_private_ip_is_never_looked_up(): void {
        config(['defender.country_access.countries' => ['ES']]);
        Http::fake();

        $this->post('/test-country')->assertOk(); // 127.0.0.1

        Http::assertNothingSent();
    }

    public function test_failed_lookup_is_cached_instead_of_retried_every_request(): void {
        config(['defender.country_access.countries' => ['ES']]);
        Http::fake(['http://ip-api.com/*' => Http::response(['status' => 'fail'], 200)]);

        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country')->assertOk();
        }

        Http::assertSentCount(1);
    }

    public function test_global_whitelist_bypasses_country_check(): void {
        config([
            'defender.country_access.countries' => ['ES'],
            'defender.whitelist.ips' => ['8.8.0.0/16'],
        ]);
        Http::fake();

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->post('/test-country')->assertOk();

        Http::assertNothingSent();
    }
}
