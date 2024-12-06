<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApiConfiguration extends Model
{

    protected $fillable = [
        'code',
        'username',
        'password',
        'access_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'extra_data' => 'array',
        ];
    }
}
