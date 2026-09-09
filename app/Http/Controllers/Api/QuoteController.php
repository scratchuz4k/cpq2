<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Services\QuoteService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Support\Facades\DB;

class QuoteController extends BaseController
{
    protected $model = Quote::class;

    public function __construct(protected QuoteService $quoteService)
    {
        parent::__construct();
    }

    /**
     * List of Quotes
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<QuoteResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Quote::class);

        $user = request()->user();

        if ($user->can('viewAny quote')) {
            // CPQ-530: grouped users only see quotes whose creator AND account
            // share one of their groups; ungrouped users see everything.
            $dataProvider->builder = $dataProvider->builder->visibleToGroups($user);
        } else {
            // viewOwn quote (or no quote-view permission) → own quotes only.
            $dataProvider->builder = $dataProvider->builder->where('quotes.user_id', $user->id);
        }

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return QuoteResource::collection($data);
    }

    /**
     * Store Quote
     */
    public function store(QuoteCreateRequest $request)
    {
        try {
            $quote = DB::transaction(fn () => $this->quoteService->store($request->all()));
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteResource($quote->refresh()));
    }

    /**
     * Show Quote
     */
    public function show(Quote $quote)
    {
        return new QuoteResource($quote);
    }

    /**
     * Update Quote
     */
    public function update(QuoteUpdateRequest $request, Quote $quote)
    {
        if ($quote->isBlocked()) {
            return handleBlockedQuote();
        }

        try {

            $quote = DB::transaction(function () use ($request, $quote) {
                $quote = $this->quoteService->update($quote, $request->all());

                return $quote;
            });
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteResource($quote->refresh()->load(['items'])));
    }

    /**
     * Remove Quote
     */
    public function destroy(Quote $quote)
    {
        $quote->delete();
    }

    /**
     * Complete Quote
     */
    #[ExcludeRouteFromDocs]
    public function complete(Quote $quote)
    {
        try {
            $quote = DB::transaction(function () use ($quote) {
                $quote = $this->quoteService->update($quote, ['status' => 'COMPLETED']);

                return $quote;
            });
        } catch (Exception $e) {
            return handleException($e);
        }

        return response()->json(new QuoteResource($quote->refresh()));
    }
}
