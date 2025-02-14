<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SolutionType extends Model
{
    use HasFactory, HasConfigurations;

    protected $fillable = ["code", "name"];

    public function tenants() : BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'solution_type_tenant');
    }
}
