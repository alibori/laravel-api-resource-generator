<?php

namespace Alibori\LaravelApiResourceGenerator\Tests;

use Alibori\LaravelApiResourceGenerator\LaravelApiResourceGeneratorServiceProvider;
use CreateUsersTable;
use Orchestra\Testbench\TestCase as Orchestra;


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
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__ . '/../database/migrations/create_users_table.php.stub';
        (new CreateUsersTable())->up();
    }
}