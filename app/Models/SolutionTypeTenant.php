<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;

class SolutionTypeTenant extends Model
{
    use SetTenantId;

    protected $table = 'solution_type_tenant';

    protected $fillable = ['solution_type_id', 'tenant_id', 'estimated_time'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function solutionType()
    {
        return $this->belongsTo(SolutionType::class, 'solution_type_id');
    }
}
