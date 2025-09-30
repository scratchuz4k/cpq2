<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = request()->user();
        $tenant = Tenant::current();
        $tenant = $currTenant ?? $user?->currentTenant;
        if (! $tenant) {
            return;
        }
        $column = $model->getConnection()->getDriverName() === 'mongodb' ? 'tenant_id' : $model->getTable().'.tenant_id';
        $builder->where($column, $tenant->id);
    }
}
