<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Metalinked\LaravelDefender\Events\SuspiciousRequestDetected;
use Metalinked\LaravelDefender\Models\BlockedIp;
use Metalinked\LaravelDefender\Services\BlocklistService;

class AutoBlockTest extends TestCase {
    private function incidents(string $ip, int $times = 1): void {
        $request = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        for ($i = 0; $i < $times; $i++) {
            event(new SuspiciousRequestDetected($request, $ip, 'test reason'));
        }
    }

    public function test_does_not_block_below_threshold(): void {
        config(['defender.auto_block.after' => 3]);

        $this->incidents('10.0.0.2', 2);

        $this->assertFalse(BlocklistService::isBlocked('10.0.0.2'));
    }

    public function test_blocks_when_threshold_reached_for_the_configured_hours(): void {
        config(['defender.auto_block.after' => 3, 'defender.auto_block.hours' => 24]);

        $this->incidents('10.0.0.3', 3);

        $blocked = BlockedIp::where('ip', '10.0.0.3')->first();
        $this->assertNotNull($blocked);
        $this->assertTrue($blocked->blocked_until->between(now()->addHours(23), now()->addHours(25)));
        $this->assertNull(Cache::get('defender:incidents:10.0.0.3'), 'counter resets after blocking');
    }

    public function test_permanent_block_when_hours_is_null(): void {
        config(['defender.auto_block.after' => 2, 'defender.auto_block.hours' => null]);

        $this->incidents('10.0.0.4', 2);

        $this->assertNull(BlockedIp::where('ip', '10.0.0.4')->value('blocked_until'));
        $this->assertTrue(BlocklistService::isBlocked('10.0.0.4'));
    }

    public function test_disabled_when_after_is_null(): void {
        config(['defender.auto_block.after' => null]);

        $this->incidents('10.0.0.1', 50);

        $this->assertDatabaseMissing('defender_blocked_ips', ['ip' => '10.0.0.1']);
    }

    public function test_does_not_double_block_already_blocked_ip(): void {
        config(['defender.auto_block.after' => 2]);

        $this->incidents('10.0.0.6', 7);

        $this->assertSame(1, BlockedIp::where('ip', '10.0.0.6')->count());
    }

    public function test_skipped_when_blocklist_disabled(): void {
        config(['defender.auto_block.after' => 1, 'defender.blocklist.enabled' => false]);

        $this->incidents('10.0.0.7', 5);

        $this->assertDatabaseMissing('defender_blocked_ips', ['ip' => '10.0.0.7']);
    }

    public function test_skipped_in_monitor_mode(): void {
        config(['defender.auto_block.after' => 1, 'defender.mode' => 'monitor']);

        $this->incidents('10.0.0.8', 5);

        $this->assertDatabaseMissing('defender_blocked_ips', ['ip' => '10.0.0.8']);
    }
}
