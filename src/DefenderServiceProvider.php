<?php

namespace Metalinked\LaravelDefender;

use Illuminate\Support\ServiceProvider;

class DefenderServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any package services.
     */
    public function boot() {
        // 1. Publish configuration, views, and migrations
        $this->publishes([
            __DIR__.'/../config/defender.php' => config_path('defender.php'),
        ], 'defender-config');

        $this->publishes([
            __DIR__.'/../resources/views/components/honeypot.blade.php' => resource_path('views/vendor/defender/components/honeypot.blade.php'),
        ], 'defender-views');

        $this->publishes([
            __DIR__.'/../database/migrations/2025_06_01_000001_create_defender_ip_logs_table.php' => database_path('migrations/2025_06_01_000001_create_defender_ip_logs_table.php'),
        ], 'defender-migrations');

        // 2. Load views and translations
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'defender');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'defender');

        // 3. Register Blade directives
        \Illuminate\Support\Facades\Blade::directive('defenderHoneypot', function () {
            return "<?php echo view('defender::components.honeypot')->render(); ?>";
        });

        // 4. Register middlewares
        $this->app['router']->aliasMiddleware('defender.honeypot', \Metalinked\LaravelDefender\Http\Middleware\HoneypotMiddleware::class);
        $this->app['router']->aliasMiddleware('defender.iplogger', \Metalinked\LaravelDefender\Http\Middleware\IpLoggerMiddleware::class);

        // 5. Register global honeypot middleware if enabled in config
        if (config('defender.honeypot.auto_protect_forms') && config('defender.honeypot.enabled')) {
            $this->app['router']->pushMiddlewareToGroup('web', \Metalinked\LaravelDefender\Http\Middleware\HoneypotAutoMiddleware::class);
        }
    }

    /**
     * Register any application services.
     */
    public function register() {
        // Merge package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/defender.php', 'defender');

        // Register Artisan commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Metalinked\LaravelDefender\Console\Commands\ShowIpLogs::class,
                \Metalinked\LaravelDefender\Console\Commands\DefenderAuditCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\DefenderExportLogsCommand::class,
                \Metalinked\LaravelDefender\Console\Commands\PruneLogsCommand::class,
            ]);
        }
    }
}
