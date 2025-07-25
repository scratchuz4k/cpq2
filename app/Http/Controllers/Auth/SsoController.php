<?php

namespace App\Http\Controllers\Auth;

use App\Models\Resource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Controller;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/LoginSso', [
            'canGoogleLogin' => (env('GOOGLE_OIDC_CLIENT_ID') && env('GOOGLE_OIDC_CLIENT_SECRET')),
            'status' => session('status'),
        ]);
    }

    // SAML2 Login
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $domain = substr(strrchr((string) $email, '@'), 1);
        $domainRecord = Tenant::where('domain', $domain)->first();
        if (! $domainRecord || ! $domainRecord->sso_id) {
            return back()->with([
                'status' => __('No SSO provider was found for your domain'),
            ]);
        }

        $resourceFile = Resource::where('id', $domainRecord->sso_id)->where('tenant_id', $domainRecord->id)->first();
        if (! $resourceFile || ! Storage::disk('public')->exists($resourceFile->getRouteValue())) {
            return back()->with([
                'status' => __('No SSO provider was found for your domain'),
            ]);
        }

        $ssoXmlContent = Storage::disk('public')->get($resourceFile->getRouteValue());
        if (! simplexml_load_string((string) $ssoXmlContent)) {
            return back()->with([
                'status' => __('The SSO configuration found for your domain is invalid'),
            ]);
        }

        Cache::put('saml2.metadata', $ssoXmlContent, now()->addMinutes(5));
        config(['services.saml2.metadata' => $ssoXmlContent]);

        try {
            return Inertia::location(Socialite::driver('saml2')->redirect());
        } catch (\Exception $e) {
            Log::debug('Error on socialite saml2 redirect: '.$e->getMessage());

            return back()->with([
                'status' => __('An error occurred while redirecting to your SSO provider, please contact your administrator'),
            ]);
        }
    }

    // SAML2 Callback
    public function callback()
    {
        try {
            config(['services.saml2.metadata' => Cache::get('saml2.metadata')]);
            $ssoUser = Socialite::driver('saml2')->stateless()->user();
            Log::debug('Socialite Callback Saml2 user: '.json_encode($ssoUser));
            $userData = $this->getSsoAttributes($ssoUser);

            if (! isset($userData['email'])) {
                throw new \Exception('SSO ERROR: Email not provided in user data');
            }
            $user = User::where('email', $userData['email'])->where('sso', true)->first();
            if (! $user) {
                throw new \Exception('User Not Found');
            }

            $domain = substr(strrchr($userData['email'], '@'), 1);
            $currentTenant = $user->tenants->where('domain', $domain)->first();
            if (! $currentTenant) {
                throw new \Exception("User ($user->id) Not Assigned to Any Tenant");
            }

            $user->update(['tenant_id' => $currentTenant->id]);
            $currentTenant->makeCurrent();
            Auth::login($user);
            Cache::forget('saml2.metadata');

            return redirect('/');
        } catch (\Exception $e) {
            Log::debug('Error on socialite saml2 callback: '.$e->getMessage());

            return redirect()->route('login.sso')->with(['status' => __('We were unable to log into your account using SSO, please contact your administrator')]);
        }
    }

    // Get SSO user data
    private function getSsoAttributes($ssoUser)
    {
        $attr = [];
        $attributes = $ssoUser->getRaw();
        foreach ($attributes as $attribute) {
            $attr[$attribute->getName()] = $attribute->getFirstAttributeValue();
        }
        $userData = ['id' => $ssoUser->id];
        $userData += $attr;

        if (! empty($userData[config('auth.sso.name_claim')])) {
            $userData['email'] = $userData[config('auth.sso.name_claim')];
        }

        if (! empty($userData[config('auth.sso.given_name_claim')])) {
            $userData['firstName'] = $userData[config('auth.sso.given_name_claim')];
        }

        if (! empty($userData[config('auth.sso.surname_claim')])) {
            $userData['lastName'] = $userData[config('auth.sso.surname_claim')];
        }

        return $userData;
    }
}
