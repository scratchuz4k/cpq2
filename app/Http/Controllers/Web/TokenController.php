<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\TokenCreateRequest;
use App\Models\Role;
use App\Models\Token;
use App\Models\User;
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
        return Inertia::render('Tokens/Create', [
            'users' => User::where('tenant_id', Auth::user()->tenant_id)->with('roles')->whereHas('roles', function ($query) {
                $query->whereIn('id', Role::descendants()->pluck('id'));
            })->get(),
        ]);
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TokenCreateRequest $request)
    {
        try {
            $user = User::find($request->user_id);
            $token = $user->createTokenWithTenant($request->name, Auth::user()->tenant_id);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('tokens.show', ['token' => $token->accessToken->id, 'plain-text-token' => $token->plainTextToken]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Token $token)
    {
        return Inertia::render('Tokens/Show', [
            'plainTextToken' => $request->input('plain-text-token', null),
            'users' => collect([User::with('roles')->find($token->tokenable_id)]),
            'token' => $token,
        ]);
    }
}
