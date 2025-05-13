<?php

namespace App\Jobs;

use App\Enums\PricePlanEnum;
use App\Helpers\Api\ShopBlocksApi;
use App\Helpers\Files\ShopBlocksFile;
use App\Helpers\Files\ShopBlocksProducts;
use App\Helpers\FilterProvider;
use App\Models\PricePlan;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksProductsJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    public $products = [];

    public $errors = [];

    public $started_at = null;

    public $tempFilters = [
        'model' => [],
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(protected ShopBlocksFile $file) {}

    public function middleware()
    {
        return [(new WithoutOverlapping('sync:ShopBlocksProducts'))->expireAfter(180)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $start = now();
        Log::info("ShopBlocksProductsJob | Started at $start");
        $page = 0;
        $synced = 0;
        $filters = [
            'updatedAfter' => null,
        ];

        $this->started_at = now();
        $content = $this->file->getContent();

        foreach ($content['data'] as $temp) {
            $this->products[$temp['sku']] = $temp;
            $filters['updatedAfter'] = $temp['updated'] > $filters['updatedAfter'] ? $temp['updated'] : $filters['updatedAfter'];
        }

        $filters['updatedAfter'] = $content['finished_at'] == null ? null : $filters['updatedAfter'];

        do {
            $page++;
            $res = (new ShopBlocksApi)->getArticles(200, $page, $filters);
            if (! array_key_exists('data', $res)) {
                break;
            }
            $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;

            $pricePlanId = PricePlan::where('code', PricePlanEnum::SINGLE->name)->value('id');
            foreach ($res['data'] as $key => $product) {
                if (count($product['children']) == 0) {
                    continue;
                }
                $sku = $product['children'][0]['sku'];
                $this->products[$sku] = $this->getProduct($product);
                $this->products[$sku]['price_plan_id'] = $pricePlanId;
                ShopBlocksProducts::updateOrCreate(
                    ['sku' => 'ABC123'],
                    ['price' => 79.99, 'name' => 'Sample Product']
                );
                // $this->tempFilters = FilterProvider::process($this->tempFilters, $this->products[$sku], true);
            }

            // $this->file->write(array_values($this->products), true, null, $this->started_at, $this->tempFilters, $this->errors);
            $synced += count($res['data']);
        } while ($page != 1);

        // $this->file->write(array_values($this->products), true, now(), $this->started_at, $this->tempFilters, $this->errors);

        $end = now();
        $date1 = Carbon::parse($start);
        $date2 = Carbon::parse($end);

        $diffInSeconds = $date2->diff($date1); // Output: 3600
        $time = $diffInSeconds->format('%y-%m-%d %h:%i:%s');
        Log::info("ShopBlocksProductsJob | Ended at $end with $synced products synced | Elapsed Time: $time");
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper(env('APP_ENV')).'] ShopBlocksProducts failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        $file = $this->file->getContent();
        $file['errors'][] = $error;

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper(env('APP_ENV')).'] ShopBlocksProducts failed with error: '.json_encode($file['errors']);

        $this->file->write($file['data'], false, now(), $file['created_at'], $file['filters'], $file['errors']);
        Log::error($message);
        if (env('DISCORD_CLOUDMARKET_JOBS')) {
            Http::post(env('DISCORD_CLOUDMARKET_JOBS'), ['content' => $message]);
        }
    }

    private function getProduct($product)
    {
        $child = $product['children'][0];
        $temp = [];

        $temp['model'] = (string) Str::of($product['name'])->words(3, '');
        $temp['sku'] = $child['sku'];
        $temp['code'] = $child['sku'];
        $temp['slug'] = $child['slug'];
        $temp['name'] = $product['name'];
        $temp['description'] = $product['description'];
        $temp['product_category_id'] = $product['category']['id'];
        $temp['brand'] = $product['brand'];
        $temp['stock'] = $product['stock'];
        $temp['primary_image'] = count($child['images']) ? $child['images'][0]['url'] : null;
        $temp['updated'] = $product['updated'];
        $temp['created'] = $product['created'];

        $temp['features'] = $this->getFeaturesFromContent($product['tabbed_content']);
        $temp['price'] = [
            'code' => 1,
            'tax_rate' => 20,
            'net' => (float) $child['price'],
            'tax' => (float) $child['price'] * 0.2,
            'gross' => (float) $child['price'] * 1.2,
        ];

        return $temp;
    }

    private function getFeaturesFromContent($contents)
    {
        $result = [];

        if (is_null($contents)) {
            return $result;
        }

        foreach ($contents as $key => $item) {
            $dom = new DOMDocument;
            libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
            $dom->loadHTML($item['content']);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
            $rows = $xpath->query('//table[@class="generic"]//tr');

            $currentSection = 'General';

            foreach ($rows as $tr) {
                $th = $tr->getElementsByTagName('th')->item(0);
                if ($th && $th->hasAttribute('colspan') && $th->getAttribute('colspan') == '2') {
                    $currentSection = trim($th->textContent);
                } else {
                    $tds = $tr->getElementsByTagName('td');
                    if ($tds->length == 2 && $currentSection) {
                        $key = trim($tds->item(0)->textContent);
                        $value = trim($tds->item(1)->textContent);
                        array_push($result, ['group' => $currentSection, 'key' => Str::slug($key), 'label' => $key, 'value' => $value]);
                    }
                }
            }

        }

        return $result;
    }
}
