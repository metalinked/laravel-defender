<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class AdvancedDetectionMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function getPackageProviders($app) {
        return [
            \Metalinked\LaravelDefender\DefenderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app) {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('defender.advanced_detection.enabled', true);
        $app['config']->set('defender.advanced_detection.suspicious_user_agents', ['sqlmap', 'curl', 'python']);
        $app['config']->set('defender.advanced_detection.suspicious_routes', ['/wp-admin', '/admin']);
        $app['config']->set('defender.advanced_detection.common_usernames', ['admin', 'root']);
        $app['config']->set('defender.alerts.channels', ['database']);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('advanced.detection')->post('/test-advanced', function () {
            return response('OK');
        });

        Route::middleware('advanced.detection')->post('/login', function () {
            return response('OK');
        });

        // Add routes for suspicious paths
        Route::middleware('advanced.detection')->any('/admin/{path?}', function () {
            return response('OK');
        })->where('path', '.*');

        Route::middleware('advanced.detection')->any('/wp-admin/{path?}', function () {
            return response('OK');
        })->where('path', '.*');
    }

    public function test_marks_log_as_suspicious_for_bad_user_agent() {
        $this->post('/test-advanced', [], ['User-Agent' => 'sqlmap']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_marks_log_as_suspicious_for_suspicious_route() {
        $response = $this->post('/admin/login');
        $this->assertEquals(429, $response->getStatusCode()); // Should return 429 (blocked)
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_marks_log_as_suspicious_for_common_username() {
        $this->post('/login', ['username' => 'admin']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }
}
