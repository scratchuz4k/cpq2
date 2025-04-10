<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\IndexRequest;
use App\Models\Token;
use App\Helpers\DataProvider;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
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
    public function index(IndexRequest $request)
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
     * Store a new Token
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
        //
    }

    /**
     * Show Token
     */
    #[ExcludeRouteFromDocs]
    public function show(Token $supplier)
    {
        //
    }

    /**
     * Update Token
     */
    #[ExcludeRouteFromDocs]
    public function update(Request $request, Token $supplier)
    {
        //
    }


    /**
     * Remove Token
     */
    public function destroy(Token $token)
    {
        $token->delete();
    }
}
