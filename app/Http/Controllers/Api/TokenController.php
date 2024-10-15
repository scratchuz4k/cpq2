<?php

namespace App\Http\Controllers\Api;

use App\Models\Token;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\TokenResource;

class TokenController extends BaseController
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(Token::class);
        try {
            $data = $dataProvider->get();
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return TokenResource::collection($data);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Token $token)
    {
        $token->delete();
    }
}
