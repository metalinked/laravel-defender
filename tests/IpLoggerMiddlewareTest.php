<?php

namespace Metalinked\LaravelDefender\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Metalinked\LaravelDefender\Models\IpLog;
use Orchestra\Testbench\TestCase;

class IpLoggerMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        Route::middleware('defender.iplogger')->post('/test-ip', function () {
            return response('ok');
        });
    }

    public function test_logs_ip_on_request()
    {
        $this->post('/test-ip');
        $this->assertDatabaseHas('ip_logs', [
            'route' => 'test-ip',
        ]);
    }
}