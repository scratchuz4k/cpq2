<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class TenantPermission
{
    public function handle($request, \Closure $next)
    {
        if (! empty(Auth::user())) {
            // session value set on login
            setPermissionsTeamId(Auth::user()->tenant_id);
        }
        // other custom ways to get team_id
        /*if(!empty(auth('api')->user())){
            // `getTeamIdFromToken()` example of custom method for getting the set team_id
            setPermissionsTeamId(auth('api')->user()->getTeamIdFromToken());
        }*/

        return $next($request);
    }
}
