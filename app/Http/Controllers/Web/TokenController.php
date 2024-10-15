<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Token;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Requests\TokenCreateRequest;

class TokenController extends BaseController
{

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
            'users'  => User::with('roles')->get()
        ]);
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function store(TokenCreateRequest $request)
    {
        try {
            $user  = User::find($request->user_id);
            $token = $user->createToken($request->name);
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
            'users'  => User::with('roles')->get(),
            'token'  => $token
        ]);
    }
}
