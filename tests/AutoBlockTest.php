<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Events\IpBlocked;
use Metalinked\LaravelDefender\Services\BlocklistService;

class AutoBlockTest extends TestCase {
    private function fireBlock(string $ip, int $times = 1): void {
        $request = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        for ($i = 0; $i < $times; $i++) {
            event(new IpBlocked($ip, 'test reason', $request));
        }
    }

    public function test_auto_block_disabled_by_default(): void {
        $this->fireBlock('10.0.0.1', 50);

        Cache::flush();
        $this->assertFalse(BlocklistService::isBlocked('10.0.0.1'));
    }

    public function test_does_not_block_below_threshold(): void {
        config(['defender.blocklist.auto_block_after' => 3]);

        $this->fireBlock('10.0.0.2', 2);

        Cache::flush();
        $this->assertFalse(BlocklistService::isBlocked('10.0.0.2'));
    }

    public function test_auto_blocks_when_threshold_reached(): void {
        config(['defender.blocklist.auto_block_after' => 3]);

        $this->fireBlock('10.0.0.3', 3);

        Cache::flush();
        $this->assertTrue(BlocklistService::isBlocked('10.0.0.3'));
    }

    public function test_auto_block_with_temporary_duration(): void {
        config([
            'defender.blocklist.auto_block_after' => 2,
            'defender.blocklist.auto_block_hours' => 1,
        ]);

        $this->fireBlock('10.0.0.4', 2);

        $blocked = \Metalinked\LaravelDefender\Models\BlockedIp::where('ip', '10.0.0.4')->first();
        $this->assertNotNull($blocked);
        $this->assertNotNull($blocked->blocked_until);
        $this->assertTrue($blocked->blocked_until->isFuture());
    }

    public function test_auto_block_resets_counter_after_blocking(): void {
        config(['defender.blocklist.auto_block_after' => 2]);

        $this->fireBlock('10.0.0.5', 2);

        // Counter should be cleared after blocking
        $this->assertNull(Cache::get('defender:auto_block_count:10.0.0.5'));
    }

    public function test_does_not_double_block_already_blocked_ip(): void {
        config(['defender.blocklist.auto_block_after' => 2]);

        // Block it once
        $this->fireBlock('10.0.0.6', 2);

        // Fire more events — should not throw or duplicate
        $this->fireBlock('10.0.0.6', 5);

        $count = \Metalinked\LaravelDefender\Models\BlockedIp::where('ip', '10.0.0.6')->count();
        $this->assertEquals(1, $count);
    }

    public function test_auto_block_skipped_when_blocklist_disabled(): void {
        config([
            'defender.blocklist.auto_block_after' => 1,
            'defender.blocklist.enabled' => false,
        ]);

        $this->fireBlock('10.0.0.7', 5);

        // Since blocklist is disabled, BlocklistService::isBlocked always returns false
        // and AutoBlockListener exits early — no DB writes
        $this->assertDatabaseMissing('defender_blocked_ips', ['ip' => '10.0.0.7']);
    }
}
