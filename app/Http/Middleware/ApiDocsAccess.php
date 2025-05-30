<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;

class ApiDocsAccess extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->can(['viewAny token', 'view token']))) {
            return $next($request);
        }

        abort(403, 'Unauthorized access to API docs.');
    }
}
