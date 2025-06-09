<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Metalinked\LaravelDefender\Alert\AlertManager;
use Orchestra\Testbench\TestCase;

class IpLoggerMiddlewareTest extends TestCase {
    use RefreshDatabase;
    
    protected function getPackageProviders($app) {
        return [
            \Metalinked\LaravelDefender\DefenderServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app) {        
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('defender.ip_logging.enabled', true);
        $app['config']->set('defender.ip_logging.log_all', true);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();
        Route::middleware('defender.iplogger')->post('/test-ip', function () {
            return response('ok');
        });
    }

    public function test_can_create_ip_log_directly() {
        \Metalinked\LaravelDefender\Models\IpLog::create([
            'ip' => '127.0.0.1',
            'route' => 'test-ip',
            'method' => 'POST',
            'user_id' => null,
            'is_suspicious' => false,
            'reason' => null,
        ]);
        $this->assertDatabaseHas('ip_logs', [
            'route' => 'test-ip',
        ]);
    }

    public function test_logs_ip_on_request() {
        $this->post('/test-ip');
        $this->assertDatabaseHas('ip_logs', [
            'route' => 'test-ip',
        ]);
    }

    public function test_marks_ip_as_suspicious_after_multiple_attempts() {
        // Simulate 5 consecutive attempts (adjust according to your logic)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/test-ip');
        }

        $this->assertDatabaseHas('ip_logs', [
            'route' => 'test-ip',
            'is_suspicious' => true,
            'reason' => 'Too many login attempts', // or the reason you have in your logic
        ]);
    }

    public function test_logs_reason_for_suspicious_activity() {
        // Simulate attempts to trigger detection
        for ($i = 0; $i < 5; $i++) {
            $this->post('/test-ip');
        }

        $log = IpLog::where('route', 'test-ip')->where('is_suspicious', true)->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('Too many login attempts', $log->reason); // or the reason you have
    }

    public function test_does_not_mark_as_suspicious_below_threshold() {
        $this->post('/test-ip');
        $this->assertDatabaseHas('ip_logs', [
            'route' => 'test-ip',
            'is_suspicious' => false,
        ]);
    }
}
