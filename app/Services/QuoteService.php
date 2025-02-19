<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\PricePlan;
use App\Models\SolutionType;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use App\Http\Resources\QuoteResource;
use Illuminate\Support\Facades\Storage;
use function Spatie\LaravelPdf\Support\pdf;

class QuoteService
{
    protected $discounsEntities = [
        'price_plans' => PricePlan::class
    ];
    public function __construct(protected QuoteItemService $quoteItemService) {}
    public function store(array $array): Quote
    {
        $quote = Quote::create($array);
        return $quote;
    }

    public function update(Quote $quote, array $array): Quote
    {
        #TODO: MOVE THIS LOGIC TO A SPECIFIC CONTROLLER
        if (array_key_exists('discounts', $array)) {
            foreach ($array['discounts'] as $key => $discount) {
                if (!array_key_exists('entity', $discount)) {
                    continue;
                }

                $model = $this->discounsEntities[$discount['entity']];
                $object = app($model)::where('code', $discount['code'])->first();

                if (!$object) {
                    continue;
                }

                $quote->discounts()->updateOrCreate([
                    "discountable_type" => $model,
                    "discountable_id" => $object->id,
                ], [
                    "discount" =>  $discount['value']
                ]);
            }
        }

        $quote->update($array);
        return $quote->refresh();
    }

    public function generatePdf(Quote $quote, $disk = "temp")
    {
        $solution_types = SolutionType::get();
        $user = request()->user();
        $tenant = $quote->tenant;
        $dir = 'pdf/' . $quote->tenant->id;

        if (!Storage::makeDirectory($dir)) {
            throw new \Exception("Could not create directory for PDFs");
        }

        $view  = array_key_exists("component", $quote->solutionType->configs) ? strtolower($quote->solutionType->configs["component"]) : "dealmaker";
        $quote = QuoteResource::make($quote->load(['items']))->resolve();

        return pdf()
            ->view("pdf.$view", compact('quote', 'solution_types', 'tenant'))
            ->format(Format::A4)
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->waitUntilNetworkIdle();
            })
            ->disk($disk);
    }
}
