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
        $dimensionTypes = [];
        $dimensions = [];
        $productDimensions = [];
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

            $temp = $this->getBrandAndModel($product, $temp);
            $temp = $this->getCategory($product, $temp);
            $temp = Product::updateOrCreate(
                ['sku' => $sku],
                [...$temp]
            );

            $this->getFeaturesFromContent($temp, $product['tabbed_content'] ?? [], $this->expire_at, $dimensionTypes, $dimensions, $productDimensions);
        }
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

    private function getCategory($product, $temp)
    {
        $category = $product['category'];
        $code = $category['id'];
        unset($category['id']);
        if (! is_null($category['parent_id'])) {
            $cat = Category::where('code', $category['parent_id'])->first();

            $category['parent_id'] = $cat?->id;
        }

        $category = Category::updateOrCreate(['code' => $code], $category);
        $temp['category_id'] = $category->id;

        return $temp;
    }

    private function getFeaturesFromContent($product, $contents, $expire_at, array &$dimensionTypes, array &$dimensions, array &$productDimensions)
    {
        $result = [];
        // ProductFeature::where('product_id', $product->id)->delete();

        if (is_null($contents)) {
            return;
        }

        foreach ($contents as $key => $item) {
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
                        $dimType = $dimensionTypes[$currentSection][$dimensionKey] ??= DimensionType::updateOrCreate(
                            ['group' => $currentSection, 'key' => $dimensionKey],
                            ['label' => $dimensionLabel]
                        );

                        $dimensionCacheKey = $dimType->id.'|'.$value;
                        $dim = $dimensions[$dimensionCacheKey] ??= Dimension::updateOrCreate(
                            ['dimension_type_id' => $dimType->id, 'value' => $value]
                        );

                        $productDimensionCacheKey = $product->id.'|'.$dimType->id;
                        if (! array_key_exists($productDimensionCacheKey, $productDimensions)) {
                            $productDimensions[$productDimensionCacheKey] = ProductDimension::firstOrCreate([
                                'dimension_type_id' => $dimType->id,
                                'product_id' => $product->id,
                            ], [
                                'dimension_id' => $dim->id,
                            ]);
                        }
                    }
                }
            }

        }
    }

    private function getBrandAndModel($product, $temp)
    {
        $brand = null;
        if (! is_null($product['brand'])) {
            unset($product['brand']['id']);
            $brand = Brand::updateOrCreate(['code' => $product['brand']['slug']], $product['brand']);
            $temp['brand_id'] = $brand->id;
        }

        $modelName = (string) Str::of($product['name'])->words(3, '');
        $model = Model::updateOrCreate(['brand_id' => $brand?->id, 'code' => Str::slug($modelName)], ['name' => $modelName]);
        $temp['model_id'] = $model->id;

        return $temp;
    }
}
