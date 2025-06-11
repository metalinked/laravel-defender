<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Orchestra\Testbench\TestCase;

class BruteForceMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('brute.force')->post('/test-brute', function () {
            return response('OK');
        });
    }

    public function test_marks_ip_as_suspicious_after_multiple_attempts() {
        config(['defender.ip_logging.max_attempts' => 2]);
        for ($i = 0; $i < 3; $i++) {
            $this->post('/test-brute');
        }
        $this->assertDatabaseHas('ip_logs', ['is_suspicious' => true]);
    }

    public function test_logs_reason_for_suspicious_activity() {
        config(['defender.ip_logging.max_attempts' => 1]);
        for ($i = 0; $i < 2; $i++) {
            $this->post('/test-brute');
        }
        $this->assertDatabaseHas('ip_logs', ['reason' => 'Too many attempts in 10 minutes']);
    }
}
