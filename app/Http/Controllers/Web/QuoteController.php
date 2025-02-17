<?php

namespace App\Http\Controllers\Web;

use App\Http\Resources\SolutionTypeResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Quote;
use App\Models\Account;
use App\Models\PricePlan;
use App\Models\SolutionType;
use App\Services\QuoteService;
use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteStatusEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\Http\Resources\QuoteResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Unit;

use function Spatie\LaravelPdf\Support\pdf;

class QuoteController extends BaseController
{
    protected $model = Quote::class;

    public function __construct(protected QuoteService $quoteService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();

        $users = User::descendants();
        if (!$user->hasRole('Sales Rep')) {
            $users = User::all();
        }

        $users = $users->filter(fn($user) => $user->quotes->count() > 0);

        return Inertia::render('Quotes/Index', [
            "users" => $users->values(),
            "solutions" => $user->currentTenant->solutionTypes,
            "status" => array_map(fn($status) => $status->name, QuoteStatusesEnum::cases())
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenant = Auth::user()->currentTenant;

        return Inertia::render('Quotes/Create', [
            'accounts' => Account::all(),
            'solution_types' => $tenant->solutionTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteCreateRequest $request)
    {
        try {
            $quote = DB::transaction(function () use ($request) {
                $quote = $this->quoteService->store($request->all());
                return $quote;
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('quotes.edit', ['quote' => $quote->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
        return Inertia::render('Quotes/Show', [
            'pricePlans' => PricePlan::all(),
            'quote' => new QuoteResource($quote->load(['items', 'orders']))
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote)
    {
        return Inertia::render('Quotes/Edit', [
            'accounts' => Account::all(),
            'solution_types' => Auth::user()->currentTenant->solutionTypes,
            'quote' => new QuoteResource($quote->load(['items']))
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteUpdateRequest $request, Quote $quote)
    {
        try {
            $quote = DB::transaction(function () use ($request, $quote) {
                return  $this->quoteService->update($quote, $request->all());
            });
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return to_route('quotes.edit', ['quote' => $quote->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        //
    }
    /**
     * TODO: maybe one day remove this method and allow editing the quote in the edit method
     */
    public function configure(Quote $quote)
    {
        return Inertia::render('Quotes/Configure', [
            'accounts' => Account::all(),
            'solution_types' => Auth::user()->currentTenant->solutionTypes,
            'quote' => new QuoteResource($quote->load(['items']))
        ]);
    }

    public function preview(Quote $quote)
    {
        return Inertia::render('Quotes/Preview', [
            'pricePlans' => PricePlan::all(),
            'quote' => new QuoteResource($quote->load(['items', 'orders']))
        ]);
    }

    public function download(Quote $quote)
    {
        $solution_types = Auth::user()->currentTenant->solutionTypes;
        $user = request()->user();
        $tenant = $quote->tenant;
        $dir = 'pdf/' . $quote->tenant->id;

        if (!Storage::makeDirectory($dir)) {
            throw new \Exception("Could not create directory for PDFs");
        }

        $view  = array_key_exists("component", $quote->solutionType->configs) ? strtolower($quote->solutionType->configs["component"]) : "dealmaker";
        $quote = QuoteResource::make($quote->load(['items']))->resolve();


        // return view("pdf.$view", compact('quote', 'solution_types'));
        return pdf()
            ->view("pdf.$view", compact('quote', 'solution_types', 'tenant'))
            ->format(Format::A4)
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->waitUntilNetworkIdle();
            })
            ->name('quote-' . $quote['id'] . '.pdf')
            ->download();
    }
}
