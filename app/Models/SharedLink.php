<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class SharedLink extends Model
{
    protected $fillable = [
        'token',
        'shareable_type',
        'shareable_id',
        'expires_at',
        'meta',
        'password'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->token = $model->token ?? Str::uuid(); // Generate token if not provided
        });
    }

    // Polymorphic relationship
    public function shareable()
    {
        return $this->morphTo()->withoutGlobalScope(TenantScope::class);
    }

    // Check if the link has expired
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && !$this->expires_at->isToday();
    }

    // Generate a URL for the shared link
    public function getUrlAttribute(): string
    {
        return url('/s/' . $this->token);
    }
    // Set password (hash it)
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Hash::make($value) : null;
    }
    // Verify password
    public function verifyPassword($password): bool
    {
        return $this->password && Hash::check($password, $this->password);
    }
}
