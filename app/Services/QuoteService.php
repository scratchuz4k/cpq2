<?php

namespace App\Services;

use App\Enums\QuoteStatusesEnum;
use App\Helpers\DataProvider;
use App\Http\Resources\QuoteItemResource;
use App\Http\Resources\QuoteResource;
use App\Http\Resources\SolutionTypeResource;
use App\Models\PricePlan;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;

use function Spatie\LaravelPdf\Support\pdf;

class QuoteService
{
    protected $discounsEntities = [
        'price_plans' => PricePlan::class,
    ];

    public function __construct(protected QuoteItemService $quoteItemService) {}

    public function store(array $array): Quote
    {
        if (
            array_key_exists('organization', $array)
            && is_array($array['organization'])
        ) {
            $array['data']['organization'] = $array['organization'];
        }

        $quote = Quote::create($array);

        if (
            array_key_exists('configurations', $array)
            && is_array($array['configurations'])
        ) {
            $quote->syncConfigurations($array['configurations']);
        }

        return $quote;
    }

    public function update(Quote $quote, array $array): Quote
    {
        // TODO: MOVE THIS LOGIC TO A SPECIFIC CONTROLLER
        if (array_key_exists('discounts', $array)) {
            foreach ($array['discounts'] as $discount) {
                if (! array_key_exists('entity', $discount)) {
                    continue;
                }

                $model = $this->discounsEntities[$discount['entity']];
                $object = app($model)::where('code', $discount['code'])->first();

                if (! $object) {
                    continue;
                }

                $quote->discounts()->updateOrCreate([
                    'discountable_type' => $model,
                    'discountable_id' => $object->id,
                ], [
                    'discount' => $discount['value'],
                ]);
            }
        }

        if (
            array_key_exists('configurations', $array)
            && is_array($array['configurations'])
        ) {
            $quote->syncConfigurations($array['configurations']);
        }

        if (
            array_key_exists('status', $array)
            && $array['status'] === QuoteStatusesEnum::REJECTED->name
        ) {
            $quote->updateQuietly($array);

            return $quote->refresh();
        }

        $quote->update($array);

        return $quote->refresh();
    }

    public function generatePdf(Request $request, Quote $quote, $disk = 'temp')
    {
        $tenant = (new \App\Http\Resources\TenantResource($quote->tenant))->resolve();
        $dir = 'pdf/'.$quote->tenant->id;

        if (! Storage::makeDirectory($dir)) {
            throw new \Exception('Could not create directory for PDFs');
        }

        $group = $quote->solutionType->tables->first()?->group ?? null;
        $groupName = $quote->solutionType->tables->first()?->group_name ?? null;
        $solutionType = SolutionTypeResource::make($quote->solutionType)->resolve();
        $quote = QuoteResource::make($quote->load(['items']))->resolve();

        if ($group) {
            $request->merge(['group' => $group]);
        }

        $request->merge(['limit' => 300]);
        $request->merge(['filters' => json_encode([['column' => 'quote_id', 'operator' => 'equals', 'value' => $quote['id']]])]);
        $dataProvider = new DataProvider(QuoteItem::class);
        $items = $dataProvider->group(QuoteItemResource::class);

        $columns = json_decode(json_encode($solutionType['table']['columns']), true);
        $view = array_key_exists('component', $solutionType['configurations']) ? strtolower((string) $solutionType['configurations']['component']) : 'dealmaker';
        if (array_key_exists('has_custom_table', $solutionType['configurations']) && $solutionType['configurations']['has_custom_table'] == '1') {
            $view = 'custom';
        }

        return pdf()
            ->view("pdf.$view", compact('quote', 'tenant', 'columns', 'items', 'group', 'groupName'))
            ->format(Format::A4)
            ->withBrowsershot(function (Browsershot $browsershot) {
                if (app()->environment(['local', 'testing'])) {
                    $browsershot->noSandbox();
                }
                $browsershot->waitUntilNetworkIdle();
            });

    }
}
