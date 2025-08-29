<?php

namespace App\Imports;

use App\Http\Requests\ProductCreateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    public $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public Tenant $tenant;

    public function __construct(protected bool $overwrite = false)
    {
        $this->tenant = auth()->user()?->currentTenant;
    }

    /**
     * Recursively processes and inserts products with missing parent_id.
     *
     * @param  array  $productsWhithParent
     * @return void
     */
    private function processProductsWithParent(?int $parentIndex = null)
    {

        if (empty($this->productsWithParent)) {
            return;
        }
        if ($parentIndex !== null) {
            $currentProduct = $this->productsWithParent[$parentIndex];
            array_splice($this->productsWithParent, $parentIndex, 1);
        } else {
            $currentProduct = array_shift($this->productsWithParent);
        }

        $parent = Product::where('sku', $currentProduct['parent_sku'])->first();

        if ($parent) {
            $this->insertProduct($currentProduct, $parent->id);
        } else {
            $productKey = array_search($currentProduct['parent_sku'], array_column($this->productsWithParent, 'id'), false);

            if ($productKey !== false) {
                $this->processProductsWithParent($productKey);

                $newParent = Product::find($currentProduct['parent_sku']);
                if (! empty($newParent)) {
                    $this->insertProduct($currentProduct, $newParent->id);
                } else {
                    $this->results['skipped']++;
                    $this->results['errors'][] = ['row' => $currentProduct, 'error' => 'Parent Product with SKU: "'.$currentProduct['parent_sku'].'" not found!'];
                }
            } else {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $currentProduct, 'error' => 'Parent Product with SKU: "'.$currentProduct['parent_sku'].'" not found!'];
            }
        }

        if ($parentIndex === null) {
            $this->processProductsWithParent();
        }
    }

    /**
     * Insert a new product with a specific product.
     *
     * @return void
     */
    private function insertProduct(array $product, ?int $parentId = null)
    {
        try {
            $rules = (new ProductCreateRequest)->rules();

            $validator = Validator::make($product, [
                ...$rules,
                'sku' => $this->overwrite
                    ? 'required|string|max:255'
                    : ['required', 'string', 'max:255', Rule::unique('products')->where('tenant_id', $this->tenant->id)],
            ]);

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $product, 'errors' => $validator->errors()->all()];

                return null;
            }

            $productData = [
                'name' => $product['name'] ?? '',
                'description' => $product['description'] ?? '',
                'expires_at' => (isset($product['expires_at']) && ! empty($product['expires_at']))
                     ? Carbon::parse($product['expires_at'])->endOfDay()
                    : null,
                'is_active' => (isset($product['is_active']) && ! empty($product['is_active'])) && (int) $product['is_active'] === 1 ? 1 : 0,
                'supplier_id' => (isset($product['supplier_id']) && ! empty($product['supplier_id'])) ? Supplier::find($product['supplier_id'])->id : null,
                'brand_id' => (isset($product['brand_id']) && ! empty($product['brand_id'])) ? Brand::find($product['brand_id'])->id : null,
                'price_plan_id' => (isset($product['price_plan_id']) && ! empty($product['price_plan_id'])) ? PricePlan::find($product['price_plan_id'])->id : null,
                'category_id' => (isset($product['category_id']) && ! empty($product['category_id'])) ? Category::find($product['category_id'])->id : null,
                'parent_id' => $parentId,
            ];

            if ($this->overwrite) {
                Product::UpdateOrCreate(
                    [
                        'sku' => $product['sku'] ?? '', 'tenant_id' => $this->tenant->id,
                    ],
                    $productData
                );
            } else {
                if (Product::where('sku', $product['sku'])->exists()) {
                    $this->results['skipped']++;
                    $this->results['errors'][] = ['row' => $product, 'error' => 'Product already exists'];

                    return;
                }
                Product::create([
                    'sku' => $product['sku'] ?? '',
                    ...$productData,
                ]);
            }
            $this->results['success']++;

        } catch (ValidationException $e) {
            $this->results['errors'][] = ['row' => $product, 'error' => $e->errors()];
        } catch (\Exception $e) {
            $this->results['errors'][] = ['row' => $product, 'error' => $e->getMessage()];
        }

        return null;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['processed']++;
            if (empty($row['parent_sku'])) {
                $this->insertProduct($row);
            } else {
                $this->productsWithParent[] = $row;
            }
        }
        $this->processProductsWithParent();
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
