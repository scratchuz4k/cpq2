<?php

namespace App\Http\Controllers\Auth;

use App\Enums\EmailTemplatesEnum;
use App\Helpers\DynamicEmail;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'canGoogleLogin' => (config('external_sources.google.oidc.client_id') && config('external_sources.google.oidc.client_secret')),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('login')->with([
                'status' => 'If an account associated with this email exists, you will receive an email with further instructions on how to reset your password.',
            ]);
        }

        $token = Password::getRepository()->create($user);

        Mail::queue(
            new DynamicEmail(
                user: $user,
                tenant: $user->currentTenant,
                templateEnum: EmailTemplatesEnum::USER_PASSWORD_RECOVERY,
                parameters: [
                    'reset_link' => route('password.reset', ['token' => $token]),
                ],
                overrides: [
                    'to' => $user->email,
                ],
            )
        );

        return redirect()->route('login')->with([
            'status' => 'If an account associated with this email exists, you will receive an email with further instructions on how to reset your password.',
        ]);
    }
}
