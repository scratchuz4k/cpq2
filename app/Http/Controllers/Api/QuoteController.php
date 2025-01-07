<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Quote;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\QuoteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\QuoteResource;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuoteController extends BaseController
{
    protected $model = Quote::class;
    public function __construct(protected QuoteService $quoteService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     * @response AnonymousResourceCollection<LengthAwarePaginator<QuoteResource>>
     */
    public function index()
    {
        $dataProvider = new DataProvider(Quote::class);

        $user = request()->user();
        $users = User::descendants();
        if (!$user->hasRole('Sales Rep')) {
            $users = User::all();
        }
        $dataProvider->builder = $dataProvider->builder->whereIn('quotes.user_id', $users->pluck('id'));

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return QuoteResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteCreateRequest $request)
    {
        try {
            $quote = DB::transaction(function () use ($request) {
                return $this->quoteService->store($request->all());
            });
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
        return response()->json(new QuoteResource($quote->refresh()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        return new QuoteResource($quote);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteUpdateRequest $request, Quote $quote)
    {
        try {
            $quote = DB::transaction(function () use ($request, $quote) {
                $quote = $this->quoteService->update($quote, $request->all());
                return $quote;
            });
        } catch (Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            return response(['message' => $e->getMessage()], 500);
        }

        return response()->json(new QuoteResource($quote->refresh()->load(['items'])));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        $quote->delete();
    }
}
