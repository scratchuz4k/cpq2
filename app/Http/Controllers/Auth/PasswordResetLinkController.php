<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Enums\EmailTemplatesEnum;
use App\Helpers\DynamicEmail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors('Utilizador inválido!');
        }

        $token = Password::getRepository()->create($user);

        Mail::queue(
            new DynamicEmail(
                tenant: $user->currentTenant,
                template: EmailTemplatesEnum::USER_PASSWORD_RECOVERY,
                parameters: [
                    'reset_link' => route('password.reset', ['token' => $token]),
                ],
                overrides: [
                    'to' => $user->email,
                ],
            )
        );

        return back();
    }
}
