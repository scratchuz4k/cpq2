<?php

namespace App\Imports;

use App\Enums\ProductStatusesEnum;
use App\Http\Requests\ProductCreateRequest;
use App\Models\PricePlan;
use App\Models\Product;
use App\Rules\TenantExistsRule;
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

    public function __construct(protected bool $overwrite = false, protected ?int $tenant_id = null) {}

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
            $validator = Validator::make($product, $this->getValidationRules());

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $product, 'errors' => $validator->errors()->all()];

                return null;
            }

            $productData = [
                'name' => $product['name'] ?? '',
                'description' => $product['description'] ?? '',
                'expires_at' => (isset($product['expires_at']) && ! empty($product['expires_at']))
                     ? Carbon::createFromFormat('d/m/Y', '21/10/2025')->endOfDay()
                    : null,
                'status' => (isset($product['status']) && ! empty($product['status'])) ? $product['status'] : ProductStatusesEnum::ACTIVE->name,
                'supplier_id' => $product['supplier_id'] ?: null,
                'price_plan_id' => (isset($product['price_plan_id']) && ! empty($product['price_plan_id'])) ? PricePlan::find($product['price_plan_id'])->id : null,
                'category_id' => $product['category_id'] ?? null,
                'parent_id' => $parentId,
                'tenant_id' => $this->tenant_id,
                'is_active' => 1,
            ];

            if ($this->overwrite) {
                Product::UpdateOrCreate(
                    [
                        'sku' => $product['sku'] ?? '', 'tenant_id' => $this->tenant_id,
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
            //            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $row = collect($row)->map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            })->toArray();
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

    public static function getImportTemplate()
    {
        $dummyProducts = Product::factory()->count(10)->make();

        $dummyProducts->each(function ($item) {
            $item->price_code = fake()->numberBetween(1, 999);
            $item->price_cost = fake()->numberBetween(1, 999);
            $item->price_net = fake()->numberBetween(1, 999);
            $item->parent_sku = fake()->optional()->slug(2);
            $item->supplier_id = fake()->numberBetween(1, 99);
            $item->category_id = fake()->numberBetween(1, 99);
        });

        $data = $dummyProducts->map(function ($item) {
            return [
                'sku' => $item->sku,
                'name' => $item->name,
                'description' => $item->description,
                'supplier_id' => $item->supplier_id,
                'category_id' => $item->category_id,
                'status' => $item->status,
                'expires_at' => Carbon::parse($item->expires_at)->format('d/m/Y'),
                'price_code' => $item->price_code,
                'price_cost' => $item->price_cost,
                'price_net' => $item->price_net,
                'price_plan_id' => $item->price_plan_id,
                'parent_sku' => $item->parent_sku,
            ];
        });

        $filename = 'import_products_template.csv';

        return [
            'data' => $data,
            'filename' => $filename,
        ];
    }

    private function getValidationRules(): array
    {
        return [
            ...(new ProductCreateRequest)->rules(),
            'supplier_id' => [
                'nullable',
                new TenantExistsRule('suppliers', $this->tenant_id),
            ],
            'category_id' => [
                'required',
                new TenantExistsRule('categories', $this->tenant_id),
            ],
            'parent_id' => [
                'nullable',
                new TenantExistsRule('products', $this->tenant_id),
            ],
            'sku' => $this->overwrite
                ? 'required|string|max:255'
                : ['required', 'string', 'max:255', Rule::unique('products')->where('tenant_id', $this->tenant_id)],
        ];
    }
}
