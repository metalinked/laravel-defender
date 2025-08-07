<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Orchestra\Testbench\TestCase;

class IpLoggerMiddlewareTest extends TestCase
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
        $app['config']->set('defender.ip_logging.enabled', true);
        $app['config']->set('defender.ip_logging.log_all', true);
        $app['config']->set('defender.alerts.channels', ['database']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        Route::middleware('ip.logger')->post('/test-ip', function () {
            return response('OK');
        });
    }

    public function test_can_create_ip_log_directly()
    {
        $log = IpLog::create([
            'ip' => '127.0.0.1',
            'route' => '/test',
            'method' => 'POST',
        ]);
        $this->assertDatabaseHas('defender_ip_logs', ['ip' => '127.0.0.1']);
    }

    public function test_logs_ip_on_request()
    {
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

    public function test_does_not_mark_as_suspicious_below_threshold()
    {
        config(['defender.ip_logging.max_attempts' => 5]);
        for ($i = 0; $i < 3; $i++) {
            $this->post('/test-ip');
        }
        $this->assertDatabaseMissing('defender_ip_logs', ['is_suspicious' => true]);
    }
}
