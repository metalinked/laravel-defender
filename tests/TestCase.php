<?php

namespace Metalinked\LaravelDefender\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Metalinked\LaravelDefender\DefenderServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            DefenderServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Defender configuration
        $app['config']->set('defender.ip_logging.enabled', true);
        $app['config']->set('defender.ip_logging.log_all', true);
        $app['config']->set('defender.alerts.channels', ['database']);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'sync');
    }
}
