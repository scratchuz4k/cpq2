<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;

class SetTenantByRequest
{
    public function handle($request, Closure $next)
    {
        $tenant = Tenant::findOrFail(1);
        session(['tenant_id' => $tenant->id]);
        $tenant->makeCurrent();

        return $next($request);
    }
}
