<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = ['code', 'name', 'sort_order', 'tenant_id', 'is_active', 'parent_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    /**
     * Get all parent categories (ancestors) up to the root
     *
     * @return \Illuminate\Support\Collection
     */
    public function getParentTree()
    {
        $parents = collect();
        $current = $this->parent;

        while ($current) {
            $parents->push($current);
            $current = $current->parent;
        }

        return $parents;
    }
}
