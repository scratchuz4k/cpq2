<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;

class SetTenantBySession
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            $temp = Tenant::findOrFail(1);
            $temp->makeCurrent();

            return $next($request);
        }

        if (! $user->currentTenant && $user->hasRole('Super Admin')) {
            $temp = Tenant::findOrFail(1);
            $user = $request->user();
            $user->update(['tenant_id' => $temp->id]);
            $user->refresh();
        }

        if (! $user->tenants->where('id', $user->tenant_id)->first()) {
            $tenant = $user->tenants->first();
            $user->update(['tenant_id' => $tenant->id]);
            $user->refresh();
        }

        if (! $user->currentTenant) {
            throw new \Exception("User ($user->id) Not Assigned to Any Tenant");
        }

        $user->currentTenant->makeCurrent();

        return $next($request);
    }
}
