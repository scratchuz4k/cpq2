<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\TokenCreateRequest;
use App\Models\Token;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TokenController extends BaseController
{
    protected $model = Token::class;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Tokens/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('tenant_id', Auth::user()->tenant_id)
            ->with('roles')
            ->permission(['view token', 'viewAny token', 'create token'])
            ->get();

        return Inertia::render('Tokens/Create', [
            'users' => User::getUserWithRoles($users),
        ]);
    }

    /**
     * Login api
     *
     * @return RedirectResponse
     */
    public function store(TokenCreateRequest $request)
    {
        try {
            $user = User::find($request->user_id);
            $token = $user->createTokenWithTenant($request->name, Auth::user()->tenant_id, $request->is_active);
        } catch (Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('tokens.show', ['token' => $token->accessToken->id, 'plain-text-token' => $token->plainTextToken]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Token $token)
    {
        $user = collect([User::with('roles')
            ->find($token->tokenable_id)])
            ->map(function ($user) {
                $roleName = $user->roles->pluck('name')->join(', ');
                $user->description = "{$user->name} ({$roleName})";

                return $user;
            });

        return Inertia::render('Tokens/Show', [
            'plainTextToken' => $request->input('plain-text-token', null),
            'users' => User::getUserWithRoles($user),
            'token' => $token,
        ]);
    }
}
