<?php

namespace App\Models\Cloudaudit;

use App\Enums\CloudAuditOperationEnum;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use SetTenantId;

    protected $fillable = ['tenant_id', 'code', 'name', 'email', 'data',  'status'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrganizationLog::class);
    }

    public function account()
    {
        return $this->logs->where('code', CloudAuditOperationEnum::ACCOUNT)->first();
    }

    public function email()
    {
        return $this->logs->where('code', CloudAuditOperationEnum::EMAIL)->first();
    }

    public function report()
    {
        return $this->logs->where('code', CloudAuditOperationEnum::REPORT)->first();
    }
}
