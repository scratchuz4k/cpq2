<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
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
            'canGoogleLogin' => (env("GOOGLE_OIDC_CLIENT_ID") && env("GOOGLE_OIDC_CLIENT_SECRET")),
            'status' => session('status'),
        ]);
    }
    /*
    protected function authenticated(Request $request, $user)
    {
        if ($user->sso) {
            Auth::logout();
            return redirect('/login')->withErrors(['email' => __('Your account is registered for SSO sign-in only, please select the correct sign-in option below.')]);
        }

        if ($firstTenant = $user->tenants()?->first()) {
            Log::debug("First tenant will be set in session: {$firstTenant->id}");
            session(['tenant_id' => $firstTenant->id]);
        }
    }
    */
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
