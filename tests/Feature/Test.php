<?php

declare(strict_types=1);

namespace Feature;

use Illuminate\Support\Facades\DB;

// Test to check if the migration is running well
it('database users table has been created', function (): void {
    expect(DB::table('users')->get())->toBeEmpty();
});
