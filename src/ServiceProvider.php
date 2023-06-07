<?php

namespace LinkRestrictedAccess;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../config/restricted-access.php' => config_path('restricted-access.php'),
            ], 'config');


            $this->commands([
                //
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/restricted-access.php', 'restricted-access');
    }

    protected function registerMigrations()
    {
        if (RestrictedAccess::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    protected function registerRoutes()
    {
        if (RestrictedAccess::$registersRoutes) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
