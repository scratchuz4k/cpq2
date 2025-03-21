<?php

namespace App\Http\Middleware;

use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use Inertia\Middleware;
use Illuminate\Http\Request;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user() ? $request->user()->getAllPermissions() : [],
                'roles' =>  $request->user() ? $request->user()->getRoleNames() : [],
            ],
            'tenant' =>  $request->user() ? (new TenantResource($request->user()->currentTenant))->resolve() : [],
        ];
    }
}
