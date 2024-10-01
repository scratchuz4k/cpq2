<?php

namespace App\Http\Controllers\Auth;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;

class CreateTokenController extends BaseController
{

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $tenant = Tenant::findOrFail( app("currentTenant")->id);
            $success['name'] =  $tenant->name;
            $success['token'] = $tenant->createToken('CloudMarket')->plainTextToken;

            // $user = Auth::user();
            // $success['name'] =  $user->name;
            // $success['token'] = $user->createToken('CloudMarket')->plainTextToken;

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }
}
