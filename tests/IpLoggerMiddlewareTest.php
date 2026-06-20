<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;

class IpLoggerMiddlewareTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        Route::middleware('ip.logger')->post('/test-ip', function () {
            return response('OK');
        });
    }

    public function test_can_create_ip_log_directly(): void {
        IpLog::create([
            'ip' => '127.0.0.1',
            'route' => '/test',
            'method' => 'POST',
        ]);
        $this->assertDatabaseHas('defender_ip_logs', ['ip' => '127.0.0.1']);
    }

    public function test_does_not_write_to_db_when_log_all_enabled(): void {
        config(['defender.ip_logging.log_all' => true]);

        $this->post('/test-ip');

        // IpLoggerMiddleware only writes to the log file, never to the DB
        $this->assertDatabaseMissing('defender_ip_logs', ['route' => 'test-ip']);
    }

    public function test_writes_to_log_file_when_log_all_enabled(): void {
        config(['defender.ip_logging.log_all' => true]);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn ($msg, $ctx) => $msg === '[Defender] Request logged' && $ctx['route'] === 'test-ip');

        $this->post('/test-ip');
    }

    public function test_does_not_log_when_log_all_disabled(): void {
        config(['defender.ip_logging.log_all' => false]);

        Log::shouldReceive('info')->never();

        $this->post('/test-ip');

        $this->assertDatabaseMissing('defender_ip_logs', ['route' => 'test-ip']);
    }
}
