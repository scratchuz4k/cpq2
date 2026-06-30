<?php

namespace App\Http\Controllers\Web;

use App\Enums\ContractTermUnitsEnum;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\PricePlan;
use App\Models\Product;
use App\Services\ProductsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends BaseController
{
    protected $model = Product::class;

    public function __construct(protected ProductsService $productsService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Products/Index', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Products/Create', [
            'price_plans' => PricePlan::orderBy('code', 'desc')->get(),
            'contract_term_units' => ContractTermUnitsEnum::asArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCreateRequest $request)
    {
        try {
            $product = DB::transaction(fn () => $this->productsService->store($request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {

        return Inertia::render('Products/Edit', [
            'product' => ProductResource::make($product->load(['prices', 'categories', 'steps']))->resolve(),
            'price_plans' => PricePlan::orderBy('code', 'desc')->get(),
            'contract_term_units' => ContractTermUnitsEnum::asArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        try {
            $product = DB::transaction(fn () => $this->productsService->update($product, $request->all()));
        } catch (\Exception $e) {
            return handleWebException($e);
        }

        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with([
                'status' => __('Product deleted with Success'),
            ]);
    }

    protected function transformExportData(Collection $data): Collection
    {
        $columns = collect(explode(',', request('columns', '')))->filter()->values();
        $titles = collect(explode(',', request('column_titles', '')))->filter()->values();

        if ($columns->isEmpty()) {
            return $data;
        }

        $data->load(['prices', 'categories', 'steps', 'linkedChildren', 'addonsChildren', 'bundlesChildren', 'pricePlan']);

        // json round-trip converts Eloquent Collections / model objects to plain PHP arrays
        $rows = json_decode(json_encode(ProductResource::collection($data)->toArray(request())), true);

        return new Collection(
            collect($rows)->map(function ($flat) use ($columns, $titles) {
                $row = [];
                foreach ($columns as $i => $col) {
                    $row[$titles->get($i, $col)] = $this->resolveExportValue($flat, $col);
                }

                return $row;
            })
        );
    }

    private function resolveExportValue(array $data, string $path): mixed
    {
        $segments = explode('.', $path);
        $current = $data;

        foreach ($segments as $index => $segment) {
            if (! array_key_exists($segment, (array) $current)) {
                return null;
            }

            $current = $current[$segment];

            // Hit a list before exhausting the path — map remaining segments over every item
            $remaining = implode('.', array_slice($segments, $index + 1));
            if ($remaining && is_array($current) && array_is_list($current)) {
                return collect($current)
                    ->map(fn ($item) => $this->resolveExportValue((array) $item, $remaining))
                    ->filter()
                    ->implode(', ');
            }
        }

        // Final value is itself a list with no further path — join by name
        if (is_array($current) && array_is_list($current)) {
            return collect($current)->map(fn ($item) => data_get($item, 'name'))->filter()->implode(', ');
        }

        if (is_array($current)) {
            return null;
        }

        // Format date strings to match the UI (Y-m-d H:i)
        if (is_string($current) && preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $current)) {
            try {
                return \Carbon\Carbon::parse($current)->format('Y-m-d H:i');
            } catch (\Exception) {
                // not a valid date, fall through
            }
        }

        return $current;
    }
}
