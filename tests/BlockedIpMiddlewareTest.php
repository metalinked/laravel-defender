<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Models\BlockedIp;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockedIpMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        // Prevent real HTTP calls to the geolocation API when AlertManager logs a block.
        Http::fake([
            'http://ip-api.com/*' => Http::response(['countryCode' => 'ES'], 200),
        ]);

        Route::middleware('defender.blocked')->get('/protected', function () {
            return response('OK');
        });
    }

    protected function tearDown(): void {
        \Illuminate\Support\Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_allows_non_blocked_ip(): void {
        $response = $this->get('/protected');

        $response->assertStatus(200);
    }

    public function test_blocks_permanently_blocked_ip(): void {
        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Test block',
            'blocked_until' => null,
        ]);

        $response = $this->get('/protected');

        $response->assertStatus(403);
    }

    public function test_allows_ip_after_block_expires(): void {
        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Expired block',
            'blocked_until' => now()->subMinute(),
        ]);

        $response = $this->get('/protected');

        $response->assertStatus(200);
    }

    public function test_blocks_temporarily_blocked_ip(): void {
        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Temporary block',
            'blocked_until' => now()->addHour(),
        ]);

        $response = $this->get('/protected');

        $response->assertStatus(403);
    }

    public function test_blocklist_service_unblock_removes_ip(): void {
        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Will be unblocked',
        ]);

        $this->assertTrue(BlocklistService::isBlocked('127.0.0.1'));

        BlocklistService::unblock('127.0.0.1');

        // Flush cache to ensure fresh DB check
        \Illuminate\Support\Facades\Cache::flush();

        $this->assertFalse(BlocklistService::isBlocked('127.0.0.1'));
    }

    public function test_blocklist_disabled_allows_all(): void {
        config(['defender.blocklist.enabled' => false]);

        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Should be ignored',
        ]);

        $response = $this->get('/protected');

        $response->assertStatus(200);
    }

    public function test_ip_blocked_event_and_alert_fired_on_blocklist_hit(): void {
        Event::fake([IpBlocked::class]);

        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Test block',
            'blocked_until' => null,
        ]);

        $this->get('/protected');

        Event::assertDispatched(IpBlocked::class, function ($event) {
            return $event->ip === '127.0.0.1';
        });

        $this->assertDatabaseHas('defender_ip_logs', [
            'ip' => '127.0.0.1',
            'is_suspicious' => true,
            'reason' => 'Blocked IP attempted access',
        ]);
    }

    public function test_temporary_block_stops_applying_once_expired_even_within_cache_ttl(): void {
        config(['defender.blocklist.cache_ttl' => 300]);

        BlockedIp::create([
            'ip' => '127.0.0.1',
            'reason' => 'Short block',
            'blocked_until' => now()->addSeconds(30),
        ]);

        // Populates the cache entry (blocked_until, not a plain boolean).
        $this->assertTrue(BlocklistService::isBlocked('127.0.0.1'));

        // Jump past the block's expiry but stay well within the 300s cache TTL,
        // so the next check must be served from cache, not a fresh DB query.
        \Illuminate\Support\Carbon::setTestNow(now()->addSeconds(60));

        $this->assertFalse(BlocklistService::isBlocked('127.0.0.1'));
    }
}
