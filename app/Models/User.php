<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'created_at',
        'updated_at',
    ];
}
