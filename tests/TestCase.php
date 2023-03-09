<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\Tests;

use Alibori\LaravelApiResourceGenerator\LaravelApiResourceGeneratorServiceProvider;
use CreateUsersTable;
use Illuminate\Console\Command;
use Orchestra\Testbench\TestCase as Orchestra;
use Symfony\Component\Console\Tester\CommandTester;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelApiResourceGeneratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $config = $app['config'];

        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set config values
        $config->set('laravelapiresourcegeneratorpackage.resources.namespace', 'Alibori\\LaravelApiResourceGenerator\\App\\Http\\Resources');
        $config->set('laravelapiresourcegeneratorpackage.resources.dir', 'app/Http/Resources');
        $config->set('laravelapiresourcegeneratorpackage.models.namespace', 'Alibori\\LaravelApiResourceGenerator\\App\\Models');
        $config->set('laravelapiresourcegeneratorpackage.models.dir', 'app/Models');

        // Create the users table
        include_once __DIR__.'/../database/migrations/create_users_table.php.stub';
        (new CreateUsersTable())->up();
    }

    protected function runCommand(Command $command, array $arguments = [], array $interactiveInput = []): CommandTester
    {
        $this->withoutMockingConsoleOutput();

        $command->setLaravel($this->app);

        $tester = new CommandTester($command);
        $tester->setInputs($interactiveInput);

        $tester->execute($arguments);

        return $tester;
    }
}
