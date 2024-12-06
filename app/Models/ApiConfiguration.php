<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApiConfiguration extends Model
{
    use SetTenantId;
    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'code',
        'name',
        'auth_type',
        'client_id',
        'username',
        'password',
        'token',
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

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getTokenAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
