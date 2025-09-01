<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    use SetTenantId;

    protected $fillable = [
        'name',
        'model',
        'file_id',
        'status',
        'user_id',
        'tenant_id',
        'data',
        'overwrite',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'object',
            'overwrite' => 'boolean',
        ];
    }

    public static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'file_id', 'id');
    }
}
