<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class SetTenant
{
    public function handle($request, Closure $next)
    {
        if ($tenant_id = $request->session()->get('tenant_id')) {
            $tenant = Tenant::findOrFail($tenant_id);
            Log::debug("Tenant found for request via session: {$tenant->id}");
            session(['tenant_id' => $tenant->id]);
            $tenant->makeCurrent();
            return $next($request);
        }


        return $next($request);
    }
}
