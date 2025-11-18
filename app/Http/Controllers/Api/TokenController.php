<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\TokenUpdateRequest;
use App\Http\Resources\TokenResource;
use App\Models\Token;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends BaseController
{
    protected $model = Token::class;

    public function __construct(protected Token $service)
    {
        parent::__construct();
    }

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
        } catch (Exception $e) {
            return handleException($e);
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
    public function update(TokenUpdateRequest $request, Token $token)
    {
        try {
            $token = Auth::user()->updateTokenWithTenant($token, $request->is_active);
        } catch (Exception $e) {
            return handleException($e);
        }

        return new TokenResource($token);
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
