<?php

namespace App\Http\Controllers\Api;

use App\Models\Token;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\TokenResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class TokenController extends BaseController
{

    protected $model = Token::class;

    /**
     * List of Tokens
     * @response AnonymousResourceCollection<LengthAwarePaginator<TokenResource>>
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
     * Remove Token
     */
    public function destroy(Token $token)
    {
        $token->delete();
    }
}
