<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class CountryAccessMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function getPackageProviders($app) {
        return [
            \Metalinked\LaravelDefender\DefenderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app) {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('defender.advanced_detection.country_access.mode', 'allow');
        $app['config']->set('defender.advanced_detection.country_access.countries', ['ES', 'US']);
        $app['config']->set('defender.advanced_detection.country_access.whitelist_ips', []);
        $app['config']->set('defender.alerts.channels', ['database']);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('country.access')->post('/test-country', function () {
            return response('OK');
        });
    }

    public function test_marks_log_as_suspicious_for_non_allowed_country() {
        $this->markTestSkipped('CountryAccessMiddleware requires real geolocation API - needs mocking setup');
        
        $response = $this->post('/test-country', [], ['X-Forwarded-For' => '203.0.113.1']);
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }
}
