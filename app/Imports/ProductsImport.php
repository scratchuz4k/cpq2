<?php

namespace App\Imports;

use App\Enums\ProductStatusesEnum;
use App\Http\Requests\ProductCreateRequest;
use App\Models\Price;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Tenant;
use App\Rules\TenantUniqueRule;
use App\Rules\ValidDateValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ProductsImport implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    protected $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
        'data' => [],
    ];

    protected Tenant $tenant;

    protected bool $isDryRun;

    protected array $availableSkus = [];

    public function __construct(protected bool $overwrite = false, bool $isDryRun = false)
    {
        $this->tenant = Tenant::current();
        $this->isDryRun = $isDryRun;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setDryRun(bool $isDryRun): void
    {
        $this->isDryRun = $isDryRun;
    }

    /**
     * Validate the import file.
     */
    public static function validateFile($file, bool $overwrite): array
    {
        $import = new self($overwrite, true);
        Excel::import($import, $file);

        return $import->results;
    }

    /**
     * Insert a new product
     */
    private function insertProduct(array $product, ?int $parentId = null): bool
    {
        try {
            $ignoreProductId = null;

            if ($this->overwrite && ! empty($product['sku'])) {
                $existingProduct = Product::where('sku', $product['sku'])->first();
                if ($existingProduct) {
                    $ignoreProductId = $existingProduct->id;
                }
            }

            $product['prices'] = [
                'code' => $product['price_code'],
                'net' => $product['price_net'],
                'cost' => $product['price_cost'],
            ];

            $validator = Validator::make($product, $this->getValidationRules($ignoreProductId));

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $product, 'errors' => $validator->errors()->all()];

                return false;
            }

            $this->availableSkus[] = $product['sku'];

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            $productData = [
                'name' => $product['name'] ?? '',
                'description' => $product['description'] ?? '',
                'expires_at' => (isset($product['expires_at']) && ! empty($product['expires_at']))
                    ? Carbon::createFromFormat('d/m/Y', $product['expires_at'])->endOfDay()
                    : null,
                'status' => (isset($product['status']) && ! empty($product['status'])) ? $product['status'] : ProductStatusesEnum::ACTIVE->name,
                'supplier_id' => $product['supplier_id'] ?: null,
                'price_plan_id' => (isset($product['price_plan_id']) && ! empty($product['price_plan_id'])) ? PricePlan::find($product['price_plan_id'])->id : null,
                'prices' => [
                    'code' => $product['price_code'],
                    'net' => $product['price_net'],
                    'cost' => $product['price_cost'],

                ],
                'category_id' => $product['category_id'] ?? null,
                'parent_id' => $parentId,
                'tenant_id' => $this->tenant->id,
                'is_active' => ucfirst(mb_strtolower($product['status'])) === ProductStatusesEnum::ACTIVE->value ? 1 : 0,
            ];

            if ($this->overwrite) {
                $newProduct = Product::UpdateOrCreate(
                    [
                        'sku' => $product['sku'],
                    ],
                    $productData
                );
            } else {
                if (Product::where('sku', $product['sku'])->exists()) {
                    $this->results['skipped']++;
                    $this->results['errors'][] = ['row' => $product, 'error' => 'Product already exists'];

                    return false;
                }

                $newProduct = Product::create([
                    'sku' => $product['sku'] ?? '',
                    ...$productData,
                ]);
            }

            if (count(array_filter($productData['prices'])) > 0) {
                Price::UpdateOrCreate(
                    [
                        'product_id' => $newProduct->id,
                    ],
                    [
                        ...$productData['prices'],
                        'tenant_id' => $this->tenant->id,
                    ]);
            }

            $this->results['success']++;

            return true;

        } catch (ValidationException $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = ['row' => $product, 'error' => $e->errors()];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = ['row' => $product, 'error' => $e->getMessage()];
        }

        return false;
    }

    public function collection(Collection $collection): void
    {
        $deferredRows = [];
        $fileSkus = $collection->pluck('sku')->filter()->unique()->toArray();

        foreach ($collection as $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $row = collect($row)->map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            })->toArray();
            $this->results['processed']++;
            $this->results['data'][] = $row;
            if (empty($row['parent_sku'])) {
                $this->insertProduct($row);
            } else {
                $deferredRows[] = $row;
            }
        }

        $fileProductParentSkus = collect($deferredRows)->pluck('parent_sku')->unique()->filter();

        if ($fileProductParentSkus->isNotEmpty() && $missingParentSkus = array_diff($fileProductParentSkus->toArray(), $fileSkus)) {
            $this->availableSkus = Product::query()->whereIn('sku', $missingParentSkus)->pluck('sku')->toArray();
            $this->availableSkus = array_unique(array_merge($this->availableSkus, $fileSkus));
        }

        $this->processDeferredChildren($deferredRows, $fileSkus);
    }

    private function processDeferredChildren(array $deferredRows, array $fileSkus): void
    {
        $processedInLastIteration = true;
        while (count($deferredRows) > 0 && $processedInLastIteration) {
            $processedInLastIteration = false;
            $remainingRows = [];

            foreach ($deferredRows as $row) {
                $parent = null;
                if (in_array($row['parent_sku'], $this->availableSkus)) {
                    if (! $this->isDryRun) {
                        $parent = Product::where('sku', $row['parent_sku'])->first();
                    }

                    if ($this->insertProduct($row, $parent ? $parent->id : null)) {
                        $processedInLastIteration = true;
                    }
                } else {
                    $remainingRows[] = $row;
                }
            }
            $deferredRows = $remainingRows;
        }

        if (count($deferredRows) > 0) {
            foreach ($deferredRows as $row) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $row, 'error' => 'Parent Product with SKU: "'.$row['parent_sku'].'" not found!'];
            }
        }
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
        $template = require app_path('Helpers/Import/product_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }

    private function getValidationRules($ignoreProductId = null): array
    {
        return [
            ...(new ProductCreateRequest)->rules(),
            'expires_at' => new ValidDateValidator,
            'sku' => $this->overwrite
                ? 'required|string|max:255'
                : ['required', 'string', 'max:255', new TenantUniqueRule('products', 'sku', $ignoreProductId)],
        ];
    }
}
