<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Controller;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    //Google Login
    public function create()
    {
        try {
            return Inertia::location(Socialite::driver('google')->redirect());
        } catch (\Exception $e) {
            Log::debug('Error on socialite google redirect: '.$e->getMessage());

            return redirect('/login')->with([
                'status' => __('An error occurred while redirecting to Google, please contact your administrator'),
            ]);
        }
    }

    //Google Callback
    public function callback()
    {
        try {
            $ssoUser = Socialite::driver('google')->stateless()->user();
            Log::debug('Socialite Callback Google user: '.json_encode($ssoUser));
            if (! isset($ssoUser['email'])) {
                throw new \Exception('SSO ERROR: Email not provided in user data');
            }
            $user = User::where('email', $ssoUser['email'])->first();
            if (! $user) {
                throw new \Exception('User ('.$ssoUser['email'].') not assigned to any tenant');
            }

            $user->currentTenant->makeCurrent();
            Auth::login($user);

            return redirect('/');
        } catch (\Exception $e) {
            Log::debug('Error on socialite google callback: '.$e->getMessage());

            return redirect('/login')->with([
                'status' => __('We were unable to log into your account using Google, please contact your administrator'),
            ]);
        }
    }
}
