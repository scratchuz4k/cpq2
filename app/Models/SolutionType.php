<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolutionType extends Model
{
    use HasFactory, HasConfigurations;

    protected $fillable = ["code", "name"];

    public function tenants(): BelongsToMany
    {
        return $this
            ->belongsToMany(Tenant::class, 'solution_type_tenant', 'solution_type_id', 'tenant_id')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'categories_solution_type');
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
