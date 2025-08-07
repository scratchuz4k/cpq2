<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            if ($request->expectsJson()) {
                $token = Token::findToken($request->bearerToken());
                if (($token && !$token->is_active) || !auth()->user()->is_active)
                    return response()->json([
                        'message' => __('Your account is inactive. Contact admin.')
                    ], 403);
            }

            if (!auth()->user()->is_active) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/login')->with([
                    'status' => __('Your account is inactive. Contact admin.'),
                ]);
            }
        }

        return $next($request);
    }
}
