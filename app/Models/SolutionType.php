<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SolutionType extends Model
{
    use HasConfigurations, HasFactory;

    protected $fillable = ['code', 'name'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this
            ->belongsToMany(Tenant::class, 'solution_type_tenant', 'solution_type_id', 'tenant_id')
            ->withPivot(['estimated_time', 'is_active'])
            ->withTimestamps();
    }

    public function tenant()
    {
        return request()->user() ? $this
            ->tenants()
            ->where('tenant_id', request()->user()->tenant_id)
            ->first() : null;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'categories_solution_type')
            ->withPivot('order')
            ->orderBy('pivot_order');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function tables(): MorphMany
    {
        return $this->morphMany(Table::class, 'tableable');
    }
}
