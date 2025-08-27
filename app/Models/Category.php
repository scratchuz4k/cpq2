<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = ['code', 'name', 'sort_order', 'tenant_id', 'solution_type_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function solutionType(): BelongsTo
    {
        return $this->belongsTo(SolutionType::class);
    }
}
