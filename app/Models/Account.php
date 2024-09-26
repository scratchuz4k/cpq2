<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['name', 'reference', 'tenant_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::observe(AccountObserver::class);
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
