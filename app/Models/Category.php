<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = ['code', 'name', 'tenant_id', 'is_active'];
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category', 'category_id');
    }

    public function solutionTypes(): BelongsToMany
    {
        return $this->belongsToMany(SolutionType::class, 'categories_solution_type');
    }
}
