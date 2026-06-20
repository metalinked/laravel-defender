<?php

namespace Metalinked\LaravelDefender;

use Illuminate\Support\ServiceProvider;

class DefenderServiceProvider extends ServiceProvider {
    public function boot(): void {
        // 1. Publish configuration, views, and migrations
        $this->publishes([
            __DIR__.'/../config/defender.php' => config_path('defender.php'),
        ], 'defender-config');

        $this->publishes([
            __DIR__.'/../resources/views/components/honeypot.blade.php' => resource_path('views/vendor/defender/components/honeypot.blade.php'),
        ], 'defender-views');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_06_01_000001_create_defender_ip_logs_table.php' => database_path('migrations/2025_06_01_000001_create_defender_ip_logs_table.php'),
            __DIR__.'/../database/migrations/2025_06_01_000002_create_defender_blocked_ips_table.php' => database_path('migrations/2025_06_01_000002_create_defender_blocked_ips_table.php'),
        ], 'defender-migrations');

        // 2. Load views and translations
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'defender');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'defender');

        // 3. Register Blade directives
        \Illuminate\Support\Facades\Blade::directive('defenderHoneypot', function () {
            return "<?php echo view('defender::components.honeypot')->render(); ?>";
        });

        // 4. Register route middleware aliases
        $this->app['router']->aliasMiddleware('defender.honeypot', \Metalinked\LaravelDefender\Http\Middleware\HoneypotMiddleware::class);
        $this->app['router']->aliasMiddleware('defender.iplogger', \Metalinked\LaravelDefender\Http\Middleware\IpLoggerMiddleware::class);
        $this->app['router']->aliasMiddleware('ip.logger', \Metalinked\LaravelDefender\Http\Middleware\IpLoggerMiddleware::class);
        $this->app['router']->aliasMiddleware('advanced.detection', \Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class);
        $this->app['router']->aliasMiddleware('brute.force', \Metalinked\LaravelDefender\Http\Middleware\BruteForceMiddleware::class);
        $this->app['router']->aliasMiddleware('country.access', \Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class);
        $this->app['router']->aliasMiddleware('defender.blocked', \Metalinked\LaravelDefender\Http\Middleware\BlockedIpMiddleware::class);

        // 5. Auto honeypot protection for web group (if enabled)
        if (config('defender.honeypot.auto_protect_forms') && config('defender.honeypot.enabled')) {
            $this->app['router']->pushMiddlewareToGroup('web', \Metalinked\LaravelDefender\Http\Middleware\HoneypotAutoMiddleware::class);
        }

        // 6. Register auto-block listener
        $this->app['events']->listen(
            \Metalinked\LaravelDefender\Events\IpBlocked::class,
            \Metalinked\LaravelDefender\Listeners\AutoBlockListener::class
        );

        // 7. Register Pulse card if Laravel Pulse is installed
        if (class_exists(\Laravel\Pulse\Facades\Pulse::class) && class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::component('defender-pulse-card', \Metalinked\LaravelDefender\Pulse\DefenderPulseCard::class);
        }
    }

    public function register(): void {
        $this->mergeConfigFrom(__DIR__.'/../config/defender.php', 'defender');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Metalinked\LaravelDefender\Console\Commands\ShowIpLogsCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\DefenderAuditCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\DefenderExportLogsCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\PruneLogsCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\StatsCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\BlockIpCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\UnblockIpCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\BlockListCommand::class,
            ]);
        }
    }
}
