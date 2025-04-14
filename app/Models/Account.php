<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes, SetTenantId;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['name',  'reference', 'tenant_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
