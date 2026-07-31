<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Metalinked\LaravelDefender\Services\AlertManager;
use Metalinked\LaravelDefender\Services\BlocklistService;

class AlertManagerReputationTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Cache::flush();
    }

    private function fakeAbuseIpDbResponse(int $score): void {
        Http::fake([
            'https://api.abuseipdb.com/*' => Http::response(['data' => ['abuseConfidenceScore' => $score]], 200),
            // AlertManager also enriches the log row with GeoService::getCountryCode().
            'http://ip-api.com/*' => Http::response(['countryCode' => 'ES'], 200),
        ]);
    }

    private function sendAlert(string $ip): void {
        $request = Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        AlertManager::send('Test Subject', 'Test message', [
            'ip' => $ip,
            'is_suspicious' => true,
            'request' => $request,
            'reason' => 'test reason',
        ]);
    }

    public function test_enriches_ip_log_with_reputation_score(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
        ]);

        $this->fakeAbuseIpDbResponse(55);

        $this->sendAlert('10.1.1.1');

        $this->assertDatabaseHas('defender_ip_logs', [
            'ip' => '10.1.1.1',
            'reputation_score' => 55,
        ]);
    }

    public function test_does_not_query_reputation_when_disabled(): void {
        config(['defender.reputation.enabled' => false]);

        // Fake all HTTP (GeoService still runs for country_code enrichment),
        // then assert AbuseIPDB specifically was never contacted.
        Http::fake();

        $this->sendAlert('10.1.1.2');

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'abuseipdb'));
        $this->assertDatabaseHas('defender_ip_logs', [
            'ip' => '10.1.1.2',
            'reputation_score' => null,
        ]);
    }

    public function test_auto_blocks_ip_when_score_meets_threshold_and_auto_block_enabled(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
            'defender.reputation.threshold' => 75,
            'defender.reputation.auto_block' => true,
        ]);

        $this->fakeAbuseIpDbResponse(90);

        $this->sendAlert('10.1.1.3');

        $this->assertTrue(BlocklistService::isBlocked('10.1.1.3'));
    }

    public function test_does_not_auto_block_when_auto_block_disabled(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
            'defender.reputation.threshold' => 75,
            'defender.reputation.auto_block' => false,
        ]);

        $this->fakeAbuseIpDbResponse(90);

        $this->sendAlert('10.1.1.4');

        $this->assertFalse(BlocklistService::isBlocked('10.1.1.4'));
    }

    public function test_does_not_auto_block_when_score_below_threshold(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
            'defender.reputation.threshold' => 75,
            'defender.reputation.auto_block' => true,
        ]);

        $this->fakeAbuseIpDbResponse(50);

        $this->sendAlert('10.1.1.5');

        $this->assertFalse(BlocklistService::isBlocked('10.1.1.5'));
    }

    public function test_auto_block_respects_temporary_duration(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
            'defender.reputation.threshold' => 75,
            'defender.reputation.auto_block' => true,
            'defender.reputation.auto_block_hours' => 12,
        ]);

        $this->fakeAbuseIpDbResponse(90);

        $this->sendAlert('10.1.1.6');

        $blocked = \Metalinked\LaravelDefender\Models\BlockedIp::where('ip', '10.1.1.6')->first();
        $this->assertNotNull($blocked);
        $this->assertNotNull($blocked->blocked_until);
        $this->assertTrue($blocked->blocked_until->isFuture());
    }
}
