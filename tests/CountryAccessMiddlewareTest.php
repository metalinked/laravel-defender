<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Orchestra\Testbench\TestCase;

class CountryAccessMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('country.access')->post('/test-country', function () {
            return response('OK');
        });
    }

    public function test_marks_log_as_suspicious_for_non_allowed_country() {
        // Simulate IP from a non-allowed country (geoip can be mocked)
        $this->post('/test-country', [], ['X-Forwarded-For' => '203.0.113.1']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }
}
