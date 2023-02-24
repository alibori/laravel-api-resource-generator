<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator;

use Alibori\LaravelApiResourceGenerator\Console\GenerateApiResourceCommand;
use Illuminate\Support\ServiceProvider;

class LaravelApiResourceGeneratorServiceProvider extends ServiceProvider
{
/**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravelapiresourcegeneratorpackage');
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([GenerateApiResourceCommand::class]);

        // Publish config file
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('laravelapiresourcegeneratorpackage.php'),
        ], 'config');
    }
}