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

    public $timeout = 900;

    public $products = [];

    public $errors = [];

    public $started_at = null;

    /**
     * Create a new job instance.
     */
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
        $pricePlanId = PricePlan::where('code', PricePlanEnum::SINGLE->name)->value('id');
        $brands = $this->syncBrands($res['data']);
        $categories = $this->syncCategories($res['data']);
        $featureRows = [];
        foreach ($res['data'] as $product) {
            if (! is_array($product) || empty($product['children']) || ! is_array($product['children'][0])) {
                continue;
            }
            if (($product['children'][0]['stock'] ?? 0) == 0) {
                continue;
            }
            $sku = $product['children'][0]['sku'];
            $temp = $this->getProduct($product);
            $temp['price_plan_id'] = $pricePlanId;
            $temp['expire_at'] = $this->expire_at;

            $temp = $this->getBrandAndModel($product, $temp, $brands);
            $temp = $this->getCategory($product, $temp, $categories);
            $temp = Product::updateOrCreate(
                ['sku' => $sku],
                [...$temp]
            );

            $this->getFeaturesFromContent($temp, $product['tabbed_content'] ?? [], $featureRows);
        }

        $this->syncFeatures($featureRows);
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) config('app.env')).'] ShopBlocksProducts failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
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

    private function syncCategories(array $products)
    {
        $categoryRows = collect($products)
            ->filter(fn ($product) => is_array($product)
                && is_array($product['category'] ?? null)
                && array_key_exists('id', $product['category']))
            ->map(function (array $product) {
                $category = $product['category'];
                $category['code'] = $category['id'];
                unset($category['id']);

                return $category;
            })
            ->keyBy('code');

        if ($categoryRows->isEmpty()) {
            return collect();
        }

        $codes = $categoryRows->keys()->merge(
            $categoryRows->pluck('parent_id')->filter()->unique()
        )->unique();
        $categories = Category::whereIn('code', $codes)->get()->keyBy('code');
        $missingCategories = $categoryRows->reject(fn (array $category) => $categories->has($category['code']));
        $now = now();
        $rows = $missingCategories->map(function (array $category) use ($categories, $now) {
            $parentCode = $category['parent_id'] ?? null;
            $category['parent_id'] = $categories->get($parentCode)?->id;

            return [
                ...$category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();

        if ($rows !== []) {
            Category::insert($rows);
        }

        return Category::whereIn('code', $categoryRows->keys())->get()->keyBy('code');
    }

    private function getCategory($product, $temp, $categories)
    {
        $category = $product['category'];
        $code = $category['id'] ?? null;
        $category = $categories->get($code);
        $temp['category_id'] = $category?->id;

        return $temp;
    }

    private function syncBrands(array $products)
    {
        $brandRows = collect($products)
            ->filter(fn ($product) => is_array($product)
                && is_array($product['brand'] ?? null)
                && ! empty($product['brand']['slug']))
            ->map(function (array $product) {
                $brand = $product['brand'];
                unset($brand['id']);
                $brand['code'] = $brand['slug'];

                return $brand;
            })
            ->keyBy('code');

        if ($brandRows->isEmpty()) {
            return collect();
        }

        $brands = Brand::whereIn('code', $brandRows->keys())->get()->keyBy('code');
        $missingBrands = $brandRows->reject(fn (array $brand) => $brands->has($brand['code']));

        $now = now();
        $rows = $missingBrands->map(fn (array $brand) => [
            ...$brand,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all();

        if ($rows !== []) {
            Brand::insert($rows);
        }

        return $brands->merge(
            Brand::whereIn('code', $missingBrands->keys())->get()->keyBy('code')
        );
    }

    private function getFeaturesFromContent($product, $contents, array &$featureRows)
    {
        if (is_null($contents)) {
            return;
        }

        foreach ($contents as $item) {
            if (! is_array($item) || ! array_key_exists('content', $item)) {
                continue;
            }

            $dom = new DOMDocument;
            libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
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
                } else {
                    $tds = $tr->getElementsByTagName('td');
                    if ($tds->length == 2 && $currentSection) {
                        $dimensionLabel = trim((string) $tds->item(0)->textContent);
                        $value = trim((string) $tds->item(1)->textContent);
                        $dimensionKey = Str::slug($dimensionLabel);
                        $featureRows[] = [
                            'product_id' => $product->id,
                            'group' => $currentSection,
                            'key' => $dimensionKey,
                            'label' => $dimensionLabel,
                            'value' => $value,
                        ];
                    }
                }
            }

        }
    }

    private function syncFeatures(array $featureRows): void
    {
        if ($featureRows === []) {
            return;
        }

        $now = now();
        $typeKeys = collect($featureRows)->map(fn (array $row) => $row['group'].'|'.$row['key'])->unique();
        $types = DimensionType::whereIn('group', $typeKeys->map(fn (string $key) => str($key)->before('|'))->unique())
            ->whereIn('key', $typeKeys->map(fn (string $key) => str($key)->after('|'))->unique())
            ->get()
            ->keyBy(fn (DimensionType $type) => $type->group.'|'.$type->key);

        $missingTypes = $typeKeys->filter(fn (string $key) => ! $types->has($key))->map(function (string $key) use ($featureRows, $now) {
            $row = collect($featureRows)->first(fn (array $feature) => $key === $feature['group'].'|'.$feature['key']);

            return [
                'group' => $row['group'],
                'key' => $row['key'],
                'label' => $row['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();

        if ($missingTypes !== []) {
            DimensionType::insert($missingTypes);
            $types = DimensionType::whereIn('group', $typeKeys->map(fn (string $key) => str($key)->before('|'))->unique())
                ->whereIn('key', $typeKeys->map(fn (string $key) => str($key)->after('|'))->unique())
                ->get()
                ->keyBy(fn (DimensionType $type) => $type->group.'|'.$type->key);
        }

        $dimensionKeys = collect($featureRows)->map(function (array $row) use ($types) {
            $type = $types->get($row['group'].'|'.$row['key']);

            return $type->id.'|'.$row['value'];
        })->unique();
        $dimensions = Dimension::whereIn('dimension_type_id', $types->pluck('id'))
            ->get()
            ->keyBy(fn (Dimension $dimension) => $dimension->dimension_type_id.'|'.$dimension->value);
        $missingDimensions = $dimensionKeys->filter(fn (string $key) => ! $dimensions->has($key))->map(function (string $key) use ($now) {
            [$typeId, $value] = explode('|', $key, 2);

            return [
                'dimension_type_id' => $typeId,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->all();

        if ($missingDimensions !== []) {
            Dimension::insert($missingDimensions);
            $dimensions = Dimension::whereIn('dimension_type_id', $types->pluck('id'))
                ->get()
                ->keyBy(fn (Dimension $dimension) => $dimension->dimension_type_id.'|'.$dimension->value);
        }

        $pivotRows = collect($featureRows)->map(function (array $row) use ($types, $dimensions, $now) {
            $type = $types->get($row['group'].'|'.$row['key']);
            $dimension = $dimensions->get($type->id.'|'.$row['value']);

            return [
                'dimension_type_id' => $type->id,
                'product_id' => $row['product_id'],
                'dimension_id' => $dimension->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->unique(fn (array $row) => $row['dimension_type_id'].'|'.$row['product_id'])->values()->all();

        ProductDimension::insertOrIgnore($pivotRows);
    }

    private function getBrandAndModel($product, $temp, $brands)
    {
        $brand = null;
        if (is_array($product['brand'] ?? null) && ! empty($product['brand']['slug'])) {
            $brand = $brands->get($product['brand']['slug']);
            $temp['brand_id'] = $brand?->id;
        }

        $modelName = (string) Str::of($product['name'])->words(3, '');
        $model = Model::updateOrCreate(['brand_id' => $brand?->id, 'code' => Str::slug($modelName)], ['name' => $modelName]);
        $temp['model_id'] = $model->id;

        return $temp;
    }
}
