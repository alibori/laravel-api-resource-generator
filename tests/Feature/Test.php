<?php

namespace Feature;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

// Test to check if the migration is running well
it('database users table has been created', function () {
    expect(DB::table('users')->get())->toBeEmpty();
});
