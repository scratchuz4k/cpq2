<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Controller;
use PHPUnit\Framework\Exception;

class IntegrateAuthController extends Controller
{
    public function login(Request $request, string $token)
    {
        try {
            $decrypted = Crypt::decrypt($token);
        } catch (Exception $ex) {
            abort(403);
        }

        if (! array_key_exists('expire_at', $decrypted)) {
            abort(403);
        }

        $date = Carbon::parse($decrypted['expire_at']);
        if ($date->isPast()) {
            abort(403);
        }

        $temp = new LoginRequest;
        $temp->merge(['email' => $decrypted['email'], 'password' => $decrypted['password']]);
        $temp->authenticate();

        // $temp->session()->regenerate();

        return redirect()->route('quotes.create', $request->toArray());
    }

    // Google Callback
    public function createToken(Request $request)
    {
        // Validate credentials
        $request->validate([
            'email' => 'required|email', // Use 'username' if needed
            'password' => 'required',
        ]);

        // Find user and verify credentials
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect'],
            ]);
        }

        $encrypted = Crypt::encrypt([
            'email' => $request->email,
            'password' => $request->password,
            'expire_at' => Carbon::now()->addSeconds(600),
        ]);

        return response()->json(['access_token' => $encrypted]);
    }
}
