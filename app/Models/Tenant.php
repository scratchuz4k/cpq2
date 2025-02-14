<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use HasFactory, HasApiTokens;
    protected $fillable = ['name', 'domain', 'database', 'sso_id', 'logo_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'sso_id', 'id');
    }
    public function logo(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'logo_id', 'id');
    }
    public function solutionTypes(): BelongsToMany
    {
        return $this->belongsToMany(SolutionType::class, 'solution_type_tenant');
    }
}
