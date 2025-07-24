<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use SetTenantId;

    protected $fillable = [
        'operation',
        'data',
        'activitable_id',
        'activitable_type',
        'user_id',
        'tenant_id',
    ];

    public static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'data' => 'object',
        ];
    }

    public function activitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
