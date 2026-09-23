<?php

namespace App\Jobs;

use App\Enums\PricePlanEnum;
use App\Helpers\Api\ShopBlocksApi;
use App\Models\PricePlan;
use App\Models\ShopBlocks\Brand;
use App\Models\ShopBlocks\Category;
use App\Models\ShopBlocks\Dimension;
use App\Models\ShopBlocks\DimensionType;
use App\Models\ShopBlocks\Model;
use App\Models\ShopBlocks\Product;
use App\Models\ShopBlocks\ProductDimension;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksProductsSyncJob implements NotTenantAware, ShouldQueue
{
    use Batchable, Queueable;

    public $timeout = 3600;

    public $products = [];

    public $errors = [];

    public $started_at = null;

    private array $brandCache = [];

    private array $categoryCache = [];

    private array $modelCache = [];

    private array $dimensionTypeCache = [];

    private array $dimensionCache = [];

    public function __construct(protected $expire_at, protected int $page, protected array $filters)
    {
        $this->onQueue('shopblocks');
    }

    public function middleware(): array
    {
        return [
            new RateLimited('shopblocks-api'),
        ];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = (new ShopBlocksApi)->getArticles(200, $this->page, $this->filters);
        if (! array_key_exists('data', $res)) {
            return;
        }

        $products = array_values(array_filter($res['data'], function ($product) {
            return is_array($product)
                && ! empty($product['children'])
                && is_array($product['children'][0] ?? null)
                && ($product['children'][0]['stock'] ?? 0) != 0;
        }));

        if (empty($products)) {
            return;
        }

        $pricePlanId = PricePlan::where('code', PricePlanEnum::SINGLE->name)->value('id');

        $this->preloadBrands($products);
        $this->preloadCategories($products);
        $this->preloadModels($products);

        $rows = [];
        $skuToProduct = [];

        foreach ($products as $product) {
            $sku = $product['children'][0]['sku'];
            $temp = $this->getProduct($product);
            $temp['price_plan_id'] = $pricePlanId;
            $temp['expire_at'] = $this->expire_at;
            $temp = $this->applyBrandAndModel($product, $temp);
            $temp = $this->applyCategory($product, $temp);
            $temp['created_at'] = now();
            $temp['updated_at'] = now();

            foreach (['primary_image', 'images', 'price'] as $jsonField) {
                if (isset($temp[$jsonField]) && ! is_scalar($temp[$jsonField])) {
                    $temp[$jsonField] = json_encode($temp[$jsonField]);
                }
            }

            $rows[$sku] = $temp;
            $skuToProduct[$sku] = $product;
        }

        if (empty($rows)) {
            return;
        }

        $updateColumns = array_values(array_diff(array_keys(reset($rows)), ['sku', 'created_at']));
        Product::upsert(array_values($rows), ['sku'], $updateColumns);

        $productIdBySku = Product::whereIn('sku', array_keys($rows))->pluck('id', 'sku')->all();

        $parsed = [];
        foreach ($skuToProduct as $sku => $product) {
            $parsed[$sku] = $this->parseDimensionsFromContent($product['tabbed_content'] ?? null);
        }

        $this->syncDimensions($parsed, $productIdBySku);
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        $message = '['.strtoupper((string) config('app.env')).'] ShopBlocksProducts failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        $message = '['.strtoupper((string) config('app.env')).'] ShopBlocksProducts failed with error: '.json_encode($error);

        Log::error($message);
        if (config('services.discord.shopblocks.jobs')) {
            Http::post(config('services.discord.shopblocks.jobs'), ['content' => $message]);
        }
    }

    private function getProduct($product)
    {
        $child = $product['children'][0];
        $temp = [];

        $temp['sku'] = $child['sku'];
        $temp['code'] = $child['sku'];
        $temp['slug'] = $child['slug'];
        $temp['name'] = $product['name'];
        $temp['description'] = $product['description'];
        $temp['stock'] = $product['stock'];
        $temp['primary_image'] = count($child['images']) ? $child['images'][0]['url'] : null;
        $temp['images'] = count($child['images']) ? $child['images'] : [];
        $temp['updated'] = $product['updated'];

        $temp['price'] = [
            'code' => 1,
            'cost' => (float) $child['price'],
            'net' => (float) $child['computed_price'],
        ];

        return $temp;
    }

    private function preloadBrands(array $products): void
    {
        $brands = collect($products)
            ->pluck('brand')
            ->filter(fn ($brand) => is_array($brand) && ! empty($brand['slug']))
            ->unique('slug')
            ->values();

        if ($brands->isEmpty()) {
            return;
        }

        $codes = $brands->pluck('slug')->unique();
        $existing = Brand::whereIn('code', $codes)->pluck('id', 'code');
        $rows = $brands->reject(fn ($brand) => $existing->has($brand['slug']))->map(fn ($brand) => [
            'code' => $brand['slug'],
            'long_description' => $brand['long_description'] ?? null,
            'meta_description' => $brand['meta_description'] ?? null,
            'meta_title' => $brand['meta_title'] ?? null,
            'name' => $brand['name'] ?? $brand['slug'],
            'short_description' => $brand['short_description'] ?? null,
            'slug' => $brand['slug'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all();

        if ($rows !== []) {
            Brand::insert($rows);
        }

        $this->brandCache = Brand::whereIn('code', $codes)->pluck('id', 'code')->all();
    }

    private function preloadCategories(array $products): void
    {
        $categories = collect($products)->pluck('category')->filter()->unique('id')->values();

        if ($categories->isEmpty()) {
            return;
        }

        $parentCodes = $categories->pluck('parent_id')->filter()->unique();
        $existingParents = $parentCodes->isEmpty()
            ? collect()
            : Category::whereIn('code', $parentCodes)->pluck('id', 'code');

        $codes = $categories->pluck('id')->unique();
        $existing = Category::whereIn('code', $codes)->pluck('id', 'code');
        $rows = $categories->reject(fn ($category) => $existing->has($category['id']))->map(function ($category) use ($existingParents) {
            return [
                'code' => $category['id'],
                'name' => $category['name'] ?? $category['id'],
                'children_count' => $category['children_count'] ?? 0,
                'active' => $category['active'] ?? true,
                'image' => isset($category['image']) ? json_encode($category['image']) : null,
                'long_description' => $category['long_description'] ?? null,
                'meta_description' => $category['meta_description'] ?? null,
                'meta_title' => $category['meta_title'] ?? null,
                'order_num' => $category['order_num'] ?? null,
                'parent_id' => ! empty($category['parent_id']) ? ($existingParents[$category['parent_id']] ?? null) : null,
                'path' => $category['path'] ?? null,
                'short_description' => $category['short_description'] ?? null,
                'show_products' => $category['show_products'] ?? true,
                'slug' => $category['slug'] ?? null,
                'visible' => $category['visible'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->values()->all();

        if ($rows !== []) {
            Category::insert($rows);
        }

        $this->categoryCache = Category::whereIn('code', $codes)->pluck('id', 'code')->all();
    }

    private function preloadModels(array $products): void
    {
        $rows = [];
        foreach ($products as $product) {
            $brandId = is_array($product['brand'] ?? null)
                ? ($this->brandCache[$product['brand']['slug']] ?? null)
                : null;
            if (! $brandId) {
                continue;
            }
            $modelName = (string) Str::of($product['name'])->words(3, '');
            $code = Str::slug($modelName);
            $key = $brandId.'|'.$code;

            $rows[$key] = [
                'brand_id' => $brandId,
                'code' => $code,
                'name' => $modelName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return;
        }

        $brandIds = collect($rows)->pluck('brand_id')->unique();
        $codes = collect($rows)->pluck('code')->unique();
        $existing = Model::whereIn('brand_id', $brandIds)
            ->whereIn('code', $codes)
            ->get(['id', 'brand_id', 'code'])
            ->keyBy(fn ($model) => $model->brand_id.'|'.$model->code);
        $missingRows = collect($rows)->reject(fn ($row) => $existing->has($row['brand_id'].'|'.$row['code']))->values()->all();

        if ($missingRows !== []) {
            Model::insert($missingRows);
        }

        $this->modelCache = Model::whereIn('brand_id', $brandIds)
            ->whereIn('code', $codes)
            ->get(['id', 'brand_id', 'code'])
            ->mapWithKeys(fn ($m) => [$m->brand_id.'|'.$m->code => $m->id])
            ->all();
    }

    private function applyBrandAndModel($product, $temp)
    {
        $brandId = is_array($product['brand'] ?? null)
            ? ($this->brandCache[$product['brand']['slug']] ?? null)
            : null;
        $temp['brand_id'] = $brandId;

        if (! $brandId) {
            $temp['model_id'] = null;

            return $temp;
        }

        $modelName = (string) Str::of($product['name'])->words(3, '');
        $code = Str::slug($modelName);
        $temp['model_id'] = $this->modelCache[$brandId.'|'.$code] ?? null;

        return $temp;
    }

    private function applyCategory($product, $temp)
    {
        $categoryId = $product['category']['id'] ?? null;
        $temp['category_id'] = $this->categoryCache[$categoryId] ?? null;

        return $temp;
    }

    private function parseDimensionsFromContent($contents): array
    {
        $result = [];

        if (! is_array($contents)) {
            return $result;
        }

        foreach ($contents as $item) {
            if (! is_array($item) || ! array_key_exists('content', $item)) {
                continue;
            }
            $dom = new DOMDocument;
            libxml_use_internal_errors(true);
            $content = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$item['content'];
            $dom->loadHTML($content);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table[@class="generic"]//tr');

            $currentSection = 'General';

            foreach ($rows as $tr) {
                $th = $tr->getElementsByTagName('th')->item(0);
                if ($th && $th->hasAttribute('colspan') && $th->getAttribute('colspan') == '2') {
                    $currentSection = trim((string) $th->textContent);

                    continue;
                }

                $tds = $tr->getElementsByTagName('td');
                if ($tds->length == 2 && $currentSection) {
                    $key = trim((string) $tds->item(0)->textContent);
                    $value = trim((string) $tds->item(1)->textContent);

                    $result[] = [
                        'group' => $currentSection,
                        'key' => Str::slug($key),
                        'label' => $key,
                        'value' => $value,
                    ];
                }
            }
        }

        return $result;
    }

    private function syncDimensions(array $parsedBySku, array $productIdBySku): void
    {
        $allRows = collect($parsedBySku)->flatten(1);

        if ($allRows->isEmpty()) {
            return;
        }

        // 1. DimensionType
        $typeRows = $allRows->unique(fn ($r) => $r['group'].'|'.$r['key'])
            ->map(fn ($r) => [
                'group' => $r['group'],
                'key' => $r['key'],
                'label' => $r['label'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->values()->all();

        $existingTypes = DimensionType::whereIn('group', collect($typeRows)->pluck('group')->unique())
            ->get(['id', 'group', 'key'])
            ->keyBy(fn ($type) => $type->group.'|'.$type->key);
        $missingTypes = collect($typeRows)->reject(fn ($row) => $existingTypes->has($row['group'].'|'.$row['key']))->values()->all();

        if ($missingTypes !== []) {
            DimensionType::insert($missingTypes);
        }

        $this->dimensionTypeCache = DimensionType::whereIn('group', collect($typeRows)->pluck('group')->unique())
            ->get(['id', 'group', 'key'])
            ->mapWithKeys(fn ($t) => [$t->group.'|'.$t->key => $t->id])
            ->all();

        // 2. Dimension
        $dimRows = $allRows->map(function ($r) {
            $typeId = $this->dimensionTypeCache[$r['group'].'|'.$r['key']] ?? null;

            return $typeId ? ['dimension_type_id' => $typeId, 'value' => $r['value']] : null;
        })->filter()->unique(fn ($r) => $r['dimension_type_id'].'|'.$r['value'])->values();

        $dimInsertRows = $dimRows->map(fn ($r) => [
            'dimension_type_id' => $r['dimension_type_id'],
            'value' => $r['value'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if (! empty($dimInsertRows)) {
            $existingDimensions = Dimension::whereIn('dimension_type_id', $dimRows->pluck('dimension_type_id')->unique())
                ->get(['dimension_type_id', 'value'])
                ->mapWithKeys(fn ($dimension) => [$dimension->dimension_type_id.'|'.$dimension->value => true]);
            $missingDimensions = collect($dimInsertRows)
                ->reject(fn ($row) => $existingDimensions->has($row['dimension_type_id'].'|'.$row['value']))
                ->values()
                ->all();

            if ($missingDimensions !== []) {
                Dimension::insert($missingDimensions);
            }
        }

        $typeIds = $dimRows->pluck('dimension_type_id')->unique();
        $this->dimensionCache = Dimension::whereIn('dimension_type_id', $typeIds)
            ->get(['id', 'dimension_type_id', 'value'])
            ->mapWithKeys(fn ($d) => [$d->dimension_type_id.'|'.$d->value => $d->id])
            ->all();

        $productIds = array_values($productIdBySku);

        $existingLinks = ProductDimension::whereIn('product_id', $productIds)
            ->get(['product_id', 'dimension_type_id'])
            ->map(fn ($pd) => $pd->product_id.'|'.$pd->dimension_type_id)
            ->flip();

        $toInsert = [];
        foreach ($parsedBySku as $sku => $rows) {
            $productId = $productIdBySku[$sku] ?? null;
            if (! $productId) {
                continue;
            }

            $seenForProduct = [];
            foreach ($rows as $r) {
                $typeId = $this->dimensionTypeCache[$r['group'].'|'.$r['key']] ?? null;
                $dimId = $typeId ? ($this->dimensionCache[$typeId.'|'.$r['value']] ?? null) : null;

                if (! $typeId || ! $dimId) {
                    continue;
                }

                $linkKey = $productId.'|'.$typeId;
                if (isset($existingLinks[$linkKey]) || isset($seenForProduct[$linkKey])) {
                    continue;
                }
                $seenForProduct[$linkKey] = true;

                $toInsert[] = [
                    'product_id' => $productId,
                    'dimension_type_id' => $typeId,
                    'dimension_id' => $dimId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($toInsert)) {
            ProductDimension::insertOrIgnore($toInsert);
        }
    }
}
