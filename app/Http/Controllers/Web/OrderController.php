<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderStatusesEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Account;
use App\Models\Order;
use App\Models\SolutionType;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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
        $user = request()->user();

        if (! $user->hasRole('Sales Rep')) {
            $users = User::all();
        } else {
            $users = $this->descendantsWithOrders();
        }

        $users = $users->filter(fn ($user) => $user->orders->count() > 0 || $user->id === request()->user()->id);

        return Inertia::render('Orders/Index', [
            'users' => $users->values(),
            'solutions' => $user->currentTenant->solutionTypes,
            'status' => array_map(fn ($status) => $status->name, OrderStatusesEnum::cases()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Orders/Create', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderCreateRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                return $this->orderService->store($request->all());
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
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
        $test = new OrderResource($order->load(['items']));

        return Inertia::render('Orders/Show', [
            'order' => new OrderResource($order->load(['items'])),
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
            'order' => new OrderResource($order->load(['items'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderUpdateRequest $request, Order $order)
    {
        try {
            $order = DB::transaction(function () use ($request, $order) {
                return $this->orderService->update($order, $request->all());
            });
        } catch (\Exception $e) {
            if (! env('APP_DEBUG')) {
                Log::error($e->getMessage());
            }
            throw new \Exception($e);
        }

        return to_route('orders.edit', ['order' => $order->id]);
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
            'order' => new OrderResource($order->load(['items'])),
        ]);
    }

    public function preview(Order $order)
    {
        return Inertia::render('Orders/Preview', [
            'accounts' => Account::all(),
            'solution_types' => SolutionType::get(),
            'order' => new OrderResource($order->load(['items'])),
        ]);
    }

    public function download(Order $order)
    {
        $solution_types = SolutionType::get();
        $user = request()->user();
        $dir = 'pdf/'.$user->currentTenant->id;

        if (! Storage::makeDirectory($dir)) {
            throw new \Exception('Could not create directory for PDFs');
        }

        // return view('pdf.invoice', compact('order', 'totals', 'solution_types'));

        return pdf()
            ->view('pdf.invoice', compact('order', 'solution_types'))
            ->name('order-'.$order->id.'.pdf')
            ->download();
    }

    private function descendantsWithOrders()
    {
        $users = collect([]);
        if (is_null(Auth::user())) {
            return $users;
        }
        $userDescedents = collect([Auth::user()]);
        do {
            $current = $userDescedents->pop();
            $users->push($current);
            $userDescedents->push(...$current->children()->whereHas('quotes')->get());
        } while (count($userDescedents));

        return $users;
    }
}
