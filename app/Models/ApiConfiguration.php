<?php

namespace App\Models;

use App\Traits\DealCodeUniqueSoftDeleted;
use App\Traits\RegisterActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiConfiguration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'url',
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
