<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Orchestra\Testbench\TestCase;

class AdvancedDetectionMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('advanced.detection')->post('/test-advanced', function () {
            return response('OK');
        });
    }

    public function test_marks_log_as_suspicious_for_bad_user_agent() {
        $this->post('/test-advanced', [], ['User-Agent' => 'sqlmap']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_marks_log_as_suspicious_for_suspicious_route() {
        $this->post('/admin/login');
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_marks_log_as_suspicious_for_common_username() {
        $this->post('/test-advanced', ['username' => 'admin']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }
}
