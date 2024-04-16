<?php

declare(strict_types=1);

namespace Feature;

use Alibori\LaravelApiResourceGenerator\Console\GenerateApiResourceCommand;
use Illuminate\Support\Facades\DB;

// Test to check if the migration is running well
it('database users table has been created', function (): void {
    expect(DB::table('users')->get())->toBeEmpty();
});

// Test to check if GenerateApiResourceCommand is working well
it('can generate a resource', function (): void {
    $command = $this->app->make(GenerateApiResourceCommand::class);

    $this->runCommand($command, ['model' => 'User']);

    expect(file_exists(__DIR__.'/../../app/Http/Resources/UserResource.php'))->toBeTrue();

    unlink(__DIR__.'/../../app/Http/Resources/UserResource.php');
});

// Test to check if GenerateApiResourceCommand is working well with multiple models
it('can generate a resource for multiple models', function (): void {
    $command = $this->app->make(GenerateApiResourceCommand::class);

    $this->runCommand($command, ['model' => 'User,Post']);

    expect(file_exists(__DIR__.'/../../app/Http/Resources/UserResource.php'))->toBeTrue()
        ->and(file_exists(__DIR__.'/../../app/Http/Resources/PostResource.php'))->toBeTrue();

    unlink(__DIR__.'/../../app/Http/Resources/UserResource.php');
    unlink(__DIR__.'/../../app/Http/Resources/PostResource.php');
});
