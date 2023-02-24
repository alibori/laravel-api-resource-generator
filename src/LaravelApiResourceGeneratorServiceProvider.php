<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator;

use Alibori\LaravelApiResourceGenerator\Console\GenerateApiResourceCommand;
use Illuminate\Support\ServiceProvider;

class LaravelApiResourceGeneratorServiceProvider extends ServiceProvider
{
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
    }
}