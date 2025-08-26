<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Import extends Model
{
    use SetTenantId;

    protected $fillable = [
        'name',
        'mime_type',
        'size',
        'status',
        'user_id',
        'tenant_id',
    ];

    public static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function importable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
