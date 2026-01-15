<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\IsActive;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Api extends Model
{
    use HasFactory, IsActive, SetTenantId;

    protected $table = 'api_configurations';

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'code',
        'name',
        'auth_type',
        'client_id',
        'app_id',
        'username',
        'password',
        'token',
        'extra_data',
        'tenant_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'extra_data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
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
