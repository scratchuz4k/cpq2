<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\TokenResource;
use App\Models\Token;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TokenController extends BaseController
{
    protected $model = Token::class;

    /**
     * List of Tokens
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<TokenResource>>
     */
    #[ExcludeRouteFromDocs]
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
    #[ExcludeRouteFromDocs]
    public function destroy(Token $token)
    {
        $token->delete();
    }
}
