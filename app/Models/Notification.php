<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use SetTenantId;

    protected $fillable = [
        'tenant_id',
        'sender_id',
        'user_id',
        'type',
        'description',
        'redirect_url',
        'is_read',
        'data',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
