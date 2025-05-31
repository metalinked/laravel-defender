<?php

namespace Metalinked\LaravelDefender;

use Illuminate\Support\ServiceProvider;

class DefenderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/defender.php' => config_path('defender.php'),
        ], 'defender-config');

        // Manually register the middleware in case the user wants to add it to specific routes
        $this->app['router']->aliasMiddleware('defender.honeypot', \LaravelDefender\Http\Middleware\HoneypotMiddleware::class);

        // Register global middleware for honeypot protection if configured
        if (config('defender.honeypot.auto_protect_forms') && config('defender.honeypot.enabled')) {
            $this->app['router']->pushMiddlewareToGroup('web', \LaravelDefender\Http\Middleware\HoneypotAutoMiddleware::class);
        }

        // Register the Blade directive for the honeypot component
        \Illuminate\Support\Facades\Blade::directive('defenderHoneypot', function () {
            return "<?php echo view('defender::components.honeypot')->render(); ?>";
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'defender');

        $this->publishes([
            __DIR__.'/../resources/views/components/honeypot.blade.php' => resource_path('views/vendor/defender/components/honeypot.blade.php'),
        ], 'defender-views');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/defender.php', 'defender');
    }
}
