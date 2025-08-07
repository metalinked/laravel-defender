<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;

class IpLoggerMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Route::middleware('ip.logger')->post('/test-ip', function () {
            return response('OK');
        });
    }

    public function test_can_create_ip_log_directly() {
        $log = IpLog::create([
            'ip' => '127.0.0.1',
            'route' => '/test',
            'method' => 'POST',
        ]);
        $this->assertDatabaseHas('defender_ip_logs', ['ip' => '127.0.0.1']);
    }

    public function test_logs_ip_on_request() {
        $this->post('/test-ip', [], [
            'User-Agent' => 'TestAgent',
            'Referer' => 'https://example.com',
        ]);
        $this->assertDatabaseHas('defender_ip_logs', [
            'route' => 'test-ip',
            'user_agent' => 'TestAgent',
            'referer' => 'https://example.com',
        ]);
    }

    public function test_does_not_mark_as_suspicious_below_threshold() {
        config(['defender.ip_logging.max_attempts' => 5]);
        for ($i = 0; $i < 3; $i++) {
            $this->post('/test-ip');
        }
        $this->assertDatabaseMissing('defender_ip_logs', ['is_suspicious' => true]);
    }
}
