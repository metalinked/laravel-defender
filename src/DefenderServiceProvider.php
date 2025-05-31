<?php

namespace Metalinked\LaravelDefender;

use Illuminate\Support\ServiceProvider;

class DefenderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Possibility to publish configurations, routes, views or migrations.
        // Example:
        // $this->publishes([
        //     __DIR__.'/../config/defender.php' => config_path('defender.php'),
        // ]);
    }

    public function register()
    {
        // Register bindings, configurations, etc.
        // $this->mergeConfigFrom(__DIR__.'/../config/defender.php', 'defender');
    }
}
