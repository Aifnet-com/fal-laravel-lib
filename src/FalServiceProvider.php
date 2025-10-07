<?php

namespace Aifnet\Fal;

use Illuminate\Support\ServiceProvider;

class FalServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $routes = __DIR__ . '/../routes.php';

        if (file_exists($routes)) {
            require $routes;
        }

        $migrations = __DIR__ . '/../database/migrations';

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }

        if ($this->app->runningInConsole()) {
            if (is_dir($migrations)) {
                $this->publishes([
                    $migrations => database_path('migrations'),
                ], 'fal-migrations');
            }

            $this->commands([
                \Aifnet\Fal\Console\Commands\CheckForStuckFalRequests::class,
            ]);
        }
    }
}
