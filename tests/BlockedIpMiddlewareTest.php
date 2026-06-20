<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\BlockedIp;
use Metalinked\LaravelDefender\Services\BlocklistService;

class BlockedIpMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Route::middleware('defender.blocked')->get('/protected', function () {
            return response('OK');
        });
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
}
