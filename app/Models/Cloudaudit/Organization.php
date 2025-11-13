<?php

namespace App\Models\Cloudaudit;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Organization extends Model
{
    use SetTenantId;

    protected $fillable = ['code', 'name', 'email', 'data', 'tenant_id', 'status'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
