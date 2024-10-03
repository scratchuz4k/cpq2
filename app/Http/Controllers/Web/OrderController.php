<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Order;
use App\Models\Account;
use App\Models\SolutionType;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderCreateRequest;

use App\Http\Requests\OrderUpdateRequest;
use function Spatie\LaravelPdf\Support\pdf;

class OrderController extends BaseController
{
    protected $model = Order::class;

    public function __construct(protected OrderService $orderService)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Dashboard');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Orders/Create', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = $this->orderService->store($request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('orders.edit', ['order' => $order->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return Inertia::render('Orders/Show', [
            'order' => new OrderResource($order)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        return Inertia::render('Orders/Edit', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get(),
            'order' => new OrderResource($order)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            $order = $this->orderService->update($order, $request->all());
        } catch (\Exception $e) {
            if (!env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }
        return redirect()->route('orders.edit', ['order' => $order->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
    /**
     * TODO: maybe one day remove this method and allow editing the order in the edit method
     */
    public function configure(Order $order)
    {
        return Inertia::render('Orders/Configure', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get(),
            'order' => new OrderResource($order)
        ]);
    }

    public function download(Order $order)
    {
        $totals = $order->totals();
        $solution_types = SolutionType::get();

        $dir = 'pdf/' . app('currentTenant')->id;

        if (!Storage::makeDirectory($dir)) {
            throw new \Exception("Could not create directory for PDFs");
        }

        // return view('pdf.invoice', compact('order', 'totals', 'solution_types'));

        return pdf()
            ->view('pdf.invoice', compact('order', 'totals', 'solution_types'))
            ->name('quote-' . $order->id . '.pdf')
            ->download();
    }
}
