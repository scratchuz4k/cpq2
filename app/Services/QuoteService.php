<?php

namespace App\Services;

use App\Enums\FrequencyUnitsEnum;
use App\Enums\QuoteStatusesEnum;
use App\Models\PricePlan;
use App\Models\Quote;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Spatie\Browsershot\Browsershot;

class QuoteService
{
    protected $discounsEntities = [
        'price_plans' => PricePlan::class,
    ];

    public function __construct(protected QuoteItemService $quoteItemService) {}

    public function store(array $array): Quote
    {
        if (
            array_key_exists('organisation', $array)
            && is_array($array['organisation'])
        ) {
            $array['data']['organisation'] = $array['organisation'];
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
                    // Recurring discount for a single billing frequency (Annual, Monthly, Daily)
                    'billing_frequency_unit' => in_array($discount['billing_frequency_unit'] ?? null, FrequencyUnitsEnum::names())
                        ? $discount['billing_frequency_unit']
                        : null,
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

    /**
     * Render the quote PDF with headless Chrome from the Vue quote preview (quotes.pdf route),
     * so the PDF always matches the preview / shared link view.
     */
    public function generatePdf(Quote $quote): string
    {
        $path = URL::temporarySignedRoute(
            'quotes.pdf',
            now()->addMinutes(config('cpq.pdf.link_ttl')),
            ['quote' => $quote->id],
            absolute: false
        );

        $browsershot = Browsershot::url(rtrim(config('cpq.pdf.render_url'), '/').$path)
            ->format('A4')
            ->margins(10, 6, 10, 6)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->waitForSelector('#quote-preview')
            ->timeout(60);

        if ($chromePath = config('cpq.pdf.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }

        if (app()->environment(['local', 'testing'])) {
            $browsershot->noSandbox();
        }

        // `npm run dev` runs outside the container, point Chrome's "localhost" (hot file URL) to it
        if (Vite::isRunningHot() && $viteHost = config('cpq.pdf.vite_host')) {
            $browsershot->addChromiumArguments(['host-resolver-rules' => "MAP localhost $viteHost"]);
        }

        return $browsershot->pdf();
    }
}
