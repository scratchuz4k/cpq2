<?php

namespace App\Http\Middleware;

use App\Enums\ImportsEnum;
use App\Http\Resources\TenantResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

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
    public function version(Request $request): ?string
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
        $configs = $request->user()->currentTenant?->configs ?? [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? UserResource::make($request->user())->resolve() : null,
                'permissions' => $request->user() ? $request->user()->getAllPermissions() : [],
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
            ],
            'tenant' => $request->user() ? (new TenantResource($request->user()->currentTenant))->resolve() : [],
            'customization' => [
                'header' => json_decode(array_key_exists('customization_header', $configs) ? $configs['customization_header'] : '[]'),
                'footer' => json_decode(array_key_exists('customization_footer', $configs) ? $configs['customization_footer'] : '[]'),
                'colors' => json_decode(array_key_exists('customization_colors', $configs) ? $configs['customization_colors'] : '[]'),
                'social' => json_decode(array_key_exists('customization_social', $configs) ? $configs['customization_social'] : '[]'),
            ],
            'flash' => [
                'status' => $request->session()->get('status'),
            ],
            'importModels' => fn () => ImportsEnum::asArray(),
        ];
    }
}
