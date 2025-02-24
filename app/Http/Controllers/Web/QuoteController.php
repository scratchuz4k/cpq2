<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Quote;
use App\Models\Account;
use App\Models\PricePlan;
use App\Models\SolutionType;
use App\Helpers\DynamicEmail;
use App\Services\QuoteService;
use App\Enums\QuoteStatusesEnum;
use App\Enums\EmailTemplatesEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\QuoteResource;

use App\Http\Requests\EmailSendRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QuoteCreateRequest;
use App\Http\Requests\QuoteUpdateRequest;

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
            "solutions" => SolutionType::all(),
            "status" => array_map(fn($status) => $status->name, QuoteStatusesEnum::cases())
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Quotes/Create', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get()
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
            'quote' => new QuoteResource($quote->load(['items', 'orders', 'account', 'account.contacts']))
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quote $quote)
    {
        return Inertia::render('Quotes/Edit', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get(),
            'quote' => new QuoteResource($quote->load(['items', 'account', 'account.contacts']))
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
            'solution_types' => SolutionType::get(),
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
        $pdf = $this->quoteService->generatePdf($quote);
        return $pdf->name('quote-' . $quote['id'] . '.pdf')->download();
    }

    public function email(EmailSendRequest $request, Quote $quote)
    {
        $pdf = $this->quoteService->generatePdf($quote);
        $pdf->save('quote-' . $quote['id'] . '.pdf');

        Mail::queue(
            new DynamicEmail(
                user: Auth::user(),
                tenant: Auth::user()->currentTenant,
                templateEnum: EmailTemplatesEnum::QUOTE_ACCOUNT_SHARE,
                parameters: [
                    'account_name' => $quote->account->name,
                    'quote_number' => $quote->id,
                ],
                overrides: [
                    'to' => $request->to ?? $quote->account->email,
                    'cc' => $request->cc,
                    'bcc' => $request->bcc
                ],
                files: [
                    [
                        'disk' => 'temp',
                        'path' => 'quote-' . $quote['id'] . '.pdf',
                        'title' => 'quote-' . $quote['id'] . '.pdf',
                        'mimetype' => 'application/pdf',
                    ],
                ],
            )
        );
    }
}
