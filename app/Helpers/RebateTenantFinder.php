<?php

namespace App\Helpers;

use App\Models\Tenant as ModelsTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class RebateTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        // in this method, session and auth has not been initialised
        // see Middleware/Tenant for the additional logic to decide
        // on the current tenant after sssion/auth init

        if ($tenant_id = session()->get('tenant_id')) {
            $tenant = Tenant::findOrFail($tenant_id);
            Log::debug("Tenant found for request via session: {$tenant->id}");
            return $tenant;
        }

        // check if this is an API request authenticated by Tenant
        if ($apiKey = $request->bearerToken()) {
            if ($token = PersonalAccessToken::findToken($apiKey)) {
                if ($model = $token?->tokenable()->first() ?? null) {
                    if ($model::class == ModelsTenant::class) {
                        Log::debug("Tenant found for request via API key: {$model->id}");
                        return $model;
                    } else {
                        abort(403, 'API key must be Tenant based');
                    }
                } else {
                    abort(403, 'No model found for API key');
                }
            } else {
                abort(403, 'API key not recognised');
            }
        }

        Log::debug("Tenant NOT FOUND for request (yet) - using Default Tenant 1 for now...");
        return \App\Models\Tenant::find(1);
    }
}
