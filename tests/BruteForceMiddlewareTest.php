<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;

class BruteForceMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Metalinked\LaravelDefender\DefenderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('defender.brute_force.max_attempts', 2);
        $app['config']->set('defender.brute_force.decay_minutes', 10);
        $app['config']->set('defender.alerts.channels', ['database']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware(['ip.logger', 'brute.force'])->post('/test-brute', function () {
            return response('OK');
        });
    }

    public function test_marks_ip_as_suspicious_after_multiple_attempts()
    {
        // Create some existing logs manually to simulate previous attempts
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'created_at' => now()->subMinutes(5),
        ]);
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'created_at' => now()->subMinutes(3),
        ]);
        
        // This request should trigger the brute force detection
        $response = $this->post('/test-brute');
        
        // Check if new suspicious log was created
        $this->assertDatabaseHas('defender_ip_logs', ['is_suspicious' => true]);
    }

    public function test_logs_reason_for_suspicious_activity()
    {
        // Create existing logs
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'created_at' => now()->subMinutes(5),
        ]);
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-brute',
            'method' => 'POST',
            'created_at' => now()->subMinutes(3),
        ]);
        
        // This request should trigger brute force detection
        $this->post('/test-brute');
        
        $this->assertDatabaseHas('defender_ip_logs', [
            'reason' => 'Too many login attempts',
        ]);
    }
}
