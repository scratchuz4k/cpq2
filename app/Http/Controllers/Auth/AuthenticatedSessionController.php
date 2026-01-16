<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'canGoogleLogin' => (config('external_sources.google.oidc.client_id') && config('external_sources.google.oidc.client_secret')),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
        } catch (\Exception $e) {
            return back()->with([
                'status' => $e->getMessage(),
            ]);
        }

        $request->session()->regenerate(); // Clear the permission cache to access fresh roles/permissions
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $user = Auth::user();
        setPermissionsTeamId(Auth::user()->tenant_id);

        if ($user->can('viewAny quote')) {
            return Inertia::location(route('quotes.index'));
        }

        return Inertia::location(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Inertia::location(route('index'));
    }
}
