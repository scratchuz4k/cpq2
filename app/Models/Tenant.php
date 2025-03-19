<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use HasFactory, HasApiTokens, HasConfigurations;
    protected $fillable = ['name', 'domain', 'database', 'sso_id', 'logo_id', 'orders_flow_is_active'];

    protected function casts(): array
    {
        return [
            'orders_flow_is_active' => 'boolean'
        ];
    }

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
        return $this
            ->belongsToMany(SolutionType::class, 'solution_type_tenant', 'tenant_id', 'solution_type_id')
            ->withTimestamps();
    }
}
