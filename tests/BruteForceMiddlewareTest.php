<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class BruteForceMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected function getPackageProviders($app): array {
        return [
            \Metalinked\LaravelDefender\DefenderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('defender.brute_force.max_attempts', 2);
        $app['config']->set('defender.brute_force.decay_minutes', 10);
        $app['config']->set('defender.alerts.channels', ['database']);
        $app['config']->set('cache.default', 'array');
    }

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        // Prevent real HTTP calls to geolocation APIs during tests
        \Illuminate\Support\Facades\Http::fake([
            'http://ip-api.com/*' => \Illuminate\Support\Facades\Http::response(['countryCode' => 'ES'], 200),
        ]);

        Route::middleware(['ip.logger', 'brute.force'])->post('/test-brute', function () {
            return response('OK');
        });
    }

    public function test_blocks_ip_after_too_many_suspicious_requests(): void {
        // Seed suspicious entries (is_suspicious must be true for them to count)
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => true,
            'created_at' => now()->subMinutes(5),
        ]);
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => true,
            'created_at' => now()->subMinutes(3),
        ]);

        $response = $this->post('/test-brute');

        $response->assertStatus(429);
    }

    public function test_does_not_block_when_only_non_suspicious_requests(): void {
        // Non-suspicious entries should NOT count toward brute force threshold
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => false,
            'created_at' => now()->subMinutes(5),
        ]);
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => false,
            'created_at' => now()->subMinutes(3),
        ]);

        $response = $this->post('/test-brute');

        $response->assertStatus(200);
    }

    public function test_logs_block_reason_in_database(): void {
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => true,
            'created_at' => now()->subMinutes(5),
        ]);
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'is_suspicious' => true,
            'created_at' => now()->subMinutes(3),
        ]);

        $this->post('/test-brute');

        $this->assertDatabaseHas('defender_ip_logs', [
            'is_suspicious' => true,
            'reason' => 'Too many requests from this IP',
        ]);
    }
}
