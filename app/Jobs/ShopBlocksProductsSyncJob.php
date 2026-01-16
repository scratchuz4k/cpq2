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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksProductsSyncJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

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
        foreach ($res['data'] as $product) {
            if (count($product['children']) == 0) {
                continue;
            }
            if ($product['children'][0]['stock'] == 0) {
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

            $this->getFeaturesFromContent($temp, $product['tabbed_content'], $this->expire_at);
        }
        $temp = Cache::get('shopblocks-sync-status');
        if (is_array($temp) && array_key_exists('synced', $temp)) {
            array_push($temp['synced'], $this->page);
            Cache::put('shopblocks-sync-status', ['synced' => $temp['synced'], 'last_page' => $temp['last_page'], 'started_at' => $temp['started_at']], 30);

            if (($this->page == $temp['last_page'] || count($temp['synced']) == $temp['last_page']) && config('external_sources.discord.shopblocks.jobs')) {
                $allPages = range(1, $temp['last_page']);
                $missingPages = array_diff($allPages, $temp['synced']);

                if (count($missingPages)) {
                    $res = 'Missing Pages: '.json_encode(array_values($missingPages));
                    $date1 = Carbon::parse($temp['started_at']);
                    $date2 = Carbon::parse(now());
                    $interval = $date1->diff($date2);
                    $timeDiff = sprintf('%02d:%02d:%02d', ($interval->d * 24) + $interval->h, $interval->i, $interval->s);

                    $message = '['.strtoupper(config('app.env')).'] ShopBlocksProducts Finished: '.$res.' ( Duration: '.$timeDiff.')';
                    Http::post(config('external_sources.discord.shopblocks.jobs'), ['content' => $message]);
                }
            }
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
        if (config('external_sources.discord.shopblocks.jobs')) {
            Http::post(config('external_sources.discord.shopblocks.jobs'), ['content' => $message]);
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

    private function getFeaturesFromContent($product, $contents, $expire_at)
    {
        $result = [];
        // ProductFeature::where('product_id', $product->id)->delete();

        if (is_null($contents)) {
            return;
        }

        foreach ($contents as $key => $item) {
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
                        $key = trim((string) $tds->item(0)->textContent);
                        $value = trim((string) $tds->item(1)->textContent);
                        $dimType = DimensionType::updateOrCreate(
                            ['group' => $currentSection, 'key' => Str::slug($key)],
                            ['label' => $key]
                        );

                        $dim = Dimension::updateOrCreate(
                            ['dimension_type_id' => $dimType->id, 'value' => $value]
                        );
                        if (! ProductDimension::where('dimension_type_id', $dimType->id)->where('product_id', $product->id)->first()) {
                            ProductDimension::updateOrCreate(['dimension_type_id' => $dimType->id, 'dimension_id' => $dim->id, 'product_id' => $product->id]);
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
