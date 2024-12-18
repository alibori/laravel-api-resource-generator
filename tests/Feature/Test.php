<?php

declare(strict_types=1);

namespace Feature;

use Alibori\LaravelApiResourceGenerator\Console\GenerateApiResourceCommand;
use Illuminate\Support\Facades\DB;

// Test to check if the migrations are running well
it('database users table has been created', function (): void {
    expect(DB::table('users')->get())->toBeEmpty();
});

it('database posts table has been created', function (): void {
    expect(DB::table('posts')->get())->toBeEmpty();
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
        ->and(file_get_contents(__DIR__.'/../../app/Http/Resources/UserResource.php'))->toContain('string $email')
        ->and(file_get_contents(__DIR__.'/../../app/Http/Resources/UserResource.php'))->not->toContain('string $title')
        ->and(file_exists(__DIR__.'/../../app/Http/Resources/PostResource.php'))->toBeTrue()
        ->and(file_get_contents(__DIR__.'/../../app/Http/Resources/PostResource.php'))->toContain('string $title')
        ->and(file_get_contents(__DIR__.'/../../app/Http/Resources/PostResource.php'))->not->toContain('string $name');

    unlink(__DIR__.'/../../app/Http/Resources/UserResource.php');
    unlink(__DIR__.'/../../app/Http/Resources/PostResource.php');
});
