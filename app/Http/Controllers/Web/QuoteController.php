<?php

namespace App\Http\Controllers\Web;

use App\Enums\EmailTemplatesEnum;
use App\Enums\QuoteStatusesEnum;
use App\Enums\SolutionTypesEnum;
use App\Helpers\DynamicEmail;
use App\Http\Controllers\BaseController;
use App\Http\Requests\EmailSendRequest;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\SolutionTypeResource;
use App\Models\Account;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\SolutionType;
use App\Models\User;
use App\Services\QuoteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

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
        if (Auth::user()->can('viewOwn quote')) {
            $users = User::where('id', Auth::id())->get();
        } else {
            $users = User::getChildAndOrphansUser()->get();
        }
        $solutionTypes = SolutionType::whereHas('quotes')->get();

        return Inertia::render('Quotes/Index', [
            'users' => $users->values(),
            'solutions' => $solutionTypes,
            'status' => array_map(fn ($status) => $status->name, QuoteStatusesEnum::cases()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1);

        return Inertia::render('Quotes/Create', [
            'accounts' => Account::all(),
            'solution_types' => (SolutionTypeResource::collection($solutionTypes))->resolve(),
            'steps' => SolutionTypesEnum::getSteps(),
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
        } catch (Exception $e) {
            return handleWebException($e);
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
            'quote' => new QuoteResource($quote->load(['items'])),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote)
    {
        return Inertia::render('Quotes/Edit', [
            'accounts' => Account::all(),
            'solution_types' => (SolutionTypeResource::collection(Auth::user()->currentTenant->solutionTypes))->resolve(),
            'quote' => new QuoteResource($quote->load(['items'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteUpdateRequest $request, Quote $quote)
    {
        try {
            $quote = DB::transaction(fn () => $this->quoteService->update($quote, $request->all()));
        } catch (Exception $e) {
            return handleWebException($e);
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
            'solution_types' => (SolutionTypeResource::collection(Auth::user()->currentTenant->solutionTypes))->resolve(),
            'quote' => new QuoteResource($quote->load(['items'])),
        ]);
    }

    public function preview(Quote $quote)
    {
        $t = new QuoteResource($quote->load(['items']));

        return Inertia::render('Quotes/Preview', [
            'pricePlans' => PricePlan::all(),
            'quote' => new QuoteResource($quote->load(['items'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function download(Request $request, $uuid)
    {
        $user = Auth::user();
        $quote = Quote::query()
            ->withoutGlobalScopes()
            ->with([
                'account' => fn ($query) => $query->withoutGlobalScopes(),
                'tenant',
            ])
            ->where('uuid', $uuid)
            ->first();

        if (
            ! $quote ||
            (! $user && ! $quote->sharedLink()->exists()) ||
            ($user && $user->tenant_id !== $quote->tenant_id && ! $quote->sharedLink()->exists())
        ) {
            return response()->json(['message' => 'Quote not available or not shared.'], 404);
        }

        $quote->tenant?->makeCurrent();

        $pdf = $this->quoteService->generatePdf($request, $quote);

        return $pdf
            ->name('quote-'.$quote['id'].'.pdf')
            ->download();

    }

    public function email(EmailSendRequest $request, Quote $quote)
    {
        $pdf = $this->quoteService->generatePdf($request, $quote);
        $pdf->save(storage_path('app/temp/quote-'.$quote['id'].'.pdf'));

        Mail::queue(
            new DynamicEmail(
                user: Auth::user(),
                tenant: Auth::user()->currentTenant,
                templateEnum: EmailTemplatesEnum::SEND_QUOTE_TO_ACCOUNT,
                parameters: [
                    'account_name' => $quote->account->name,
                    'quote_number' => $quote->id,
                ],
                overrides: [
                    'to' => $request->to,
                    'cc' => $request->cc,
                    'bcc' => $request->bcc,
                ],
                files: [
                    [
                        'disk' => 'temp',
                        'path' => 'quote-'.$quote['id'].'.pdf',
                        'title' => 'quote-'.$quote['id'].'.pdf',
                        'mimetype' => 'application/pdf',
                    ],
                ],
            )
        );
    }
}
