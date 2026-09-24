<?php

namespace App\Imports;

use App\Enums\FrequencyUnitsEnum;
use App\Enums\PricePlanEnum;
use App\Enums\ProductStatusesEnum;
use App\Http\Requests\ProductCreateRequest;
use App\Models\Price;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\User;
use App\Rules\BillingFrequencyRule;
use App\Rules\TenantUniqueRule;
use App\Rules\ValidDateValidator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    protected array $availableSkus = [];

    protected ?int $ignoreProductId = null;

    protected ?int $ignorePriceId = null;

    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false, protected bool $overwrite = false)
    {
        parent::__construct($this->actingUser, $model, $isDryRun);
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
     * Insert a new product
     */
    private function insertProduct(array $product): bool
    {
        try {
            if (isset($product['status'])) {
                $matchingCase = collect(ProductStatusesEnum::cases())
                    ->first(fn ($case) => strtoupper($case->value) === strtoupper($product['status']));

                if ($matchingCase) {
                    $product['status'] = $matchingCase->value;
                }
            }
            if ($this->overwrite && ! empty($product['sku'])) {
                $existingProduct = Product::where('sku', $product['sku'])->first();
                if ($existingProduct) {
                    $this->ignoreProductId = $existingProduct->id;

                    if ($existingPrice = Price::firstWhere('product_id', $existingProduct['id'])) {
                        $this->ignorePriceId = $existingPrice->id;
                    }
                }
            }

            $product['prices'] = [
                'code' => $product['price_code'] ?? null,
                'net' => $product['price_net'] ?? null,
                'cost' => $product['price_cost'] ?? null,
            ];

            if (isset($product['categories']) && is_string($product['categories'])) {
                $product['categories'] = array_filter(array_map('trim', explode(',', $product['categories'])));
            }

            if (isset($product['steps']) && is_string($product['steps'])) {
                $product['steps'] = array_filter(array_map('trim', explode(',', $product['steps'])));
            }

            if (! $this->isRecurringPricePlan($product['price_plan_id'] ?? null)) {
                $product['renewal_terms_value'] = null;
                $product['renewal_terms_unit'] = null;
                $product['billing_frequency_value'] = null;
                $product['billing_frequency_unit'] = null;
            }

            $validator = Validator::make($product, $this->getValidationRules($product));

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $product['index'] ?? null,
                    'row' => $product,
                    'errors' => $validator->errors()->all(),
                ];

                return false;
            }

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
                'price_plan_id' => (isset($product['price_plan_id']) && ! empty($product['price_plan_id'])) ? PricePlan::find($product['price_plan_id'])->id : null,
                'renewal_terms_value' => $product['renewal_terms_value'] ?? null,
                'renewal_terms_unit' => $product['renewal_terms_unit'] ?? null,
                'billing_frequency_value' => $product['billing_frequency_value'] ?? null,
                'billing_frequency_unit' => $product['billing_frequency_unit'] ?? null,
                'prices' => [
                    'code' => $product['price_code'] ?? null,
                    'net' => $product['price_net'] ?? null,
                    'cost' => $product['price_cost'] ?? null,

                ],
                'categories' => $product['categories'] ?? null,
                'steps' => $product['steps'] ?? null,
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
                    $this->results['errors'][] = [
                        'row_index' => $product['index'] ?? null,
                        'row' => $product,
                        'error' => 'Product already exists',
                    ];

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

            if (array_key_exists('categories', $product) && ! empty($product['categories'])) {
                $newProduct->categories()->sync(array_map('intval', $product['categories']));
            }

            if (array_key_exists('steps', $product) && ! empty($product['steps'])) {
                $newProduct->steps()->sync(array_map('intval', $product['steps']));
            }

            $this->results['success']++;

            return true;

        } catch (ValidationException $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $product['index'] ?? null,
                'row' => $product,
                'error' => $e->errors(),
            ];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $product['index'] ?? null,
                'row' => $product,
                'error' => $e->getMessage(),
            ];
        }

        return false;
    }

    public function collection(Collection $collection): void
    {
        Auth::setUser($this->actingUser);
        $columnKeys = $this->templateColumnKeys();

        foreach ($collection->values() as $rowIndex => $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $row = $this->normalizeRow($row, $rowIndex);
            $row = $this->useTemplateColumnKeys($row, $columnKeys);
            $row['renewal_terms_unit'] = $this->normalizeFrequencyUnit($row['renewal_terms_unit'] ?? null);
            $row['billing_frequency_unit'] = $this->normalizeFrequencyUnit($row['billing_frequency_unit'] ?? null);

            $this->results['processed']++;
            $this->results['data'][] = $row;
            $this->insertProduct($row);
        }
    }

    /**
     * Normalise a frequency unit to the FrequencyUnitsEnum name.
     * Accepts singular or plural, any case ("day", "Months", "YEAR" -> "DAYS", "MONTHS", "YEARS").
     */
    private function normalizeFrequencyUnit(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $needle = strtoupper(Str::plural(trim($value)));

        $match = collect(FrequencyUnitsEnum::cases())
            ->first(fn ($case) => strtoupper($case->name) === $needle);

        return $match?->name ?? $value;
    }

    /**
     * Whether the given price plan id refers to a recurring plan.
     */
    private function isRecurringPricePlan($pricePlanId): bool
    {
        if (empty($pricePlanId)) {
            return false;
        }

        return PricePlan::where('id', $pricePlanId)
            ->where('code', PricePlanEnum::MONTHLY->name)
            ->exists();
    }

    public static function getImportTemplate()
    {
        $template = require app_path('Helpers/Import/product_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }

    private function getValidationRules(array $product): array
    {
        $rules = (new ProductCreateRequest)->rules();

        unset(
            $rules['prices.*.code'],
            $rules['prices.*.cost'],
            $rules['prices.*.net'],
            $rules['renewal_terms_value'],
            $rules['renewal_terms_unit'],
            $rules['billing_frequency_value'],
            $rules['billing_frequency_unit'],
        );

        $recurringRule = $this->isRecurringPricePlan($product['price_plan_id'] ?? null) ? 'required' : 'nullable';

        return [
            ...$rules,
            'expires_at' => new ValidDateValidator,
            'sku' => $this->overwrite
                ? 'required|string|max:255'
                : ['required', 'string', 'max:255', new TenantUniqueRule('products', 'sku', $this->ignoreProductId)],
            'prices.code' => [
                'nullable',
                'numeric',
                Rule::unique('prices', 'code')
                    ->ignore($this->ignorePriceId)
                    ->where(fn ($q) => $q->where('product_id', $this->ignoreProductId)),
            ],
            'prices.cost' => 'required|numeric',
            'prices.net' => 'required|numeric',
            'renewal_terms_value' => [$recurringRule, 'integer', 'between:1,100'],
            'renewal_terms_unit' => [$recurringRule, Rule::in(FrequencyUnitsEnum::names())],
            'billing_frequency_value' => [$recurringRule, 'integer', new BillingFrequencyRule],
            'billing_frequency_unit' => [$recurringRule, Rule::in(FrequencyUnitsEnum::names())],
        ];
    }
}
