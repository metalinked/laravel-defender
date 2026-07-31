<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Metalinked\LaravelDefender\Detection\ReputationService;

class ReputationServiceTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Cache::flush();
    }

    private function fakeAbuseIpDbResponse(int $score, int $status = 200): void {
        Http::fake([
            'https://api.abuseipdb.com/*' => Http::response(['data' => ['abuseConfidenceScore' => $score]], $status),
        ]);
    }

    public function test_returns_null_when_reputation_disabled(): void {
        config(['defender.reputation.enabled' => false]);

        $this->assertNull(ReputationService::getScore('1.2.3.4'));
    }

    public function test_returns_null_when_no_api_key_configured(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => null,
        ]);

        // Guard against any real network call being attempted.
        Http::fake();

        $this->assertNull(ReputationService::getScore('1.2.3.4'));
        Http::assertNothingSent();
    }

    public function test_returns_score_from_abuseipdb(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
        ]);

        $this->fakeAbuseIpDbResponse(87);

        $this->assertSame(87, ReputationService::getScore('1.2.3.4'));
    }

    public function test_caches_score_and_does_not_refetch(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
            'defender.reputation.cache_minutes' => 60,
        ]);

        $this->fakeAbuseIpDbResponse(42);

        ReputationService::getScore('1.2.3.4');
        ReputationService::getScore('1.2.3.4');

        Http::assertSentCount(1);
    }

    public function test_returns_null_when_provider_unavailable(): void {
        config([
            'defender.reputation.enabled' => true,
            'defender.reputation.api_key' => 'test-key',
        ]);

        Http::fake([
            'https://api.abuseipdb.com/*' => Http::response([], 500),
        ]);

        $this->assertNull(ReputationService::getScore('1.2.3.4'));
    }
}
