<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApiConfiguration extends Model
{
    use SetTenantId;
    
    protected $fillable = [
        'code',
        'name',
        'auth_type',
        'client_id',
        'username',
        'password',
        'access_token',
        'extra_data',
        'tenant_id'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'extra_data' => 'array',
        ];
    }
}
