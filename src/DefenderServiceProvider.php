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
            __DIR__.'/../resources/views/blocked.blade.php' => resource_path('views/vendor/defender/blocked.blade.php'),
        ], 'defender-views');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_06_01_000001_create_defender_ip_logs_table.php' => database_path('migrations/2025_06_01_000001_create_defender_ip_logs_table.php'),
            __DIR__.'/../database/migrations/2025_06_01_000002_create_defender_blocked_ips_table.php' => database_path('migrations/2025_06_01_000002_create_defender_blocked_ips_table.php'),
            __DIR__.'/../database/migrations/2026_07_31_000001_add_reputation_score_to_defender_ip_logs_table.php' => database_path('migrations/2026_07_31_000001_add_reputation_score_to_defender_ip_logs_table.php'),
        ], 'defender-migrations');

        // 2. Load views and translations
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'defender');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'defender');

        // 3. Register Blade directives
        \Illuminate\Support\Facades\Blade::directive('defenderHoneypot', function () {
            return "<?php echo view('defender::components.honeypot')->render(); ?>";
        });

        // 4. Register route middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('defender', \Metalinked\LaravelDefender\Http\Middleware\DefenderMiddleware::class);
        $router->aliasMiddleware('defender.blocked', \Metalinked\LaravelDefender\Http\Middleware\BlockedIpMiddleware::class);
        $router->aliasMiddleware('defender.advanced_detection', \Metalinked\LaravelDefender\Http\Middleware\AdvancedDetectionMiddleware::class);
        $router->aliasMiddleware('defender.country_access', \Metalinked\LaravelDefender\Http\Middleware\CountryAccessMiddleware::class);
        $router->aliasMiddleware('defender.honeypot', \Metalinked\LaravelDefender\Http\Middleware\HoneypotMiddleware::class);

        // 5. Auto honeypot protection for web group (if enabled)
        if (config('defender.honeypot.auto_protect_forms') && config('defender.honeypot.enabled')) {
            $this->app['router']->pushMiddlewareToGroup('web', \Metalinked\LaravelDefender\Http\Middleware\HoneypotAutoMiddleware::class);
        }

        // 6. Listeners: escalation to the blocklist, and failed-login protection
        $events = $this->app['events'];
        $events->listen(
            \Metalinked\LaravelDefender\Events\SuspiciousRequestDetected::class,
            \Metalinked\LaravelDefender\Listeners\AutoBlockListener::class
        );

        $events->listen(\Illuminate\Auth\Events\Failed::class, [\Metalinked\LaravelDefender\Listeners\LoginProtection::class, 'failed']);
        $events->listen(\Illuminate\Auth\Events\Attempting::class, [\Metalinked\LaravelDefender\Listeners\LoginProtection::class, 'attempting']);

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
