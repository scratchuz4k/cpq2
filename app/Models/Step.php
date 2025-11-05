<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Step extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = ['name', 'sort_order', 'tenant_id', 'solution_type_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_step');
    }

    public function solutionType(): BelongsTo
    {
        return $this->belongsTo(SolutionType::class);
    }
}
