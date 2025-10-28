<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
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
        if (! $user->hasRole(['Sales Rep', 'Sales Manager'])) {
            $users = User::all();
        } else {
            $users = $user->descendants();

            if ($user->hasRole('Sales Manager') && $user->group) {
                $group_users = User::with('roles')->whereHas('roles', function ($q) use ($user) {
                    $q->where('name', $user->roles->first()->children->first()->name);
                })->where('group_id', $user->group_id)->get();

                $users = $users->merge($group_users);
            }
        }

        $dataProvider->builder = $dataProvider->builder->whereIn('quotes.user_id', $users->pluck('id'));

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
        if ($quote->isBlocked()) {
            return handleBlockedQuote();
        }

        $quote->delete();
    }

    /**
     * Complete Quote
     */
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
