<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\TokenResource;
use App\Models\Token;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            $token = DB::transaction(function () use ($token, $request) {
                return $this->service->update($token, $request->all());
            });
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            throw new Exception($e);
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
