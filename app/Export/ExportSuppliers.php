<?php

namespace App\Export;

use App\Enums\CloudMarketSuppliersEnum;
use App\Enums\SolutionTypesEnum;
use App\Models\Quote;
use App\Models\SolutionType;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportSuppliers implements WithMultipleSheets
{
    public function __construct(protected array $dateRange, protected array $columns) {}

    public static function getColumns()
    {
        $quote = new Quote;
        $quote_columns = array_diff(
            Schema::getColumnListing($quote->getTable()),
            $quote->getHidden()
        );

        return array_values($quote_columns);
    }

    public function collection($provider)
    {
        $connectivity_solution_id = SolutionType::where('code', SolutionTypesEnum::ConnectivityEthernet)->value('id');
        $data = Quote::select($this->columns)
            ->where('solution_type_id', $connectivity_solution_id)
            ->whereHas('items', function ($query) use ($provider) {
                $query->where('data->providerKey', $provider);
            })
            ->whereBetween('created_at', formated_date_range($this->dateRange, Quote::class))
            ->get();

        return $data->map(function ($quote) {
            return $quote->getAttributes();
        });
    }

    public function sheets(): array
    {
        $sheets = [];
        $providers = array_map(fn ($provider) => $provider->value, CloudMarketSuppliersEnum::cases());
        foreach ($providers as $provider) {
            $collection = $this->collection($provider);
            if ($collection->count()) {
                $sheets[$provider] = new ExportSheet($provider, $collection, $this->columns);
            }
        }

        if (is_array($sheets) && ! count($sheets)) {
            throw new \Exception('No data Found!');
        }

        return $sheets;
    }
}
