<?php

declare(strict_types=1);

namespace Alibori\LaravelApiResourceGenerator\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'title',
        'created_at',
        'updated_at',
    ];
}
