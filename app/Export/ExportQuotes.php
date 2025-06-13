<?php

namespace App\Export;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportQuotes implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(protected array $dateRange, protected array $columns) {}

    public function collection()
    {
        $data = Quote::join('quote_items', 'quotes.id', '=', 'quote_items.quote_id')
            ->select(array_map(fn ($col) => $col.' as '.$col, $this->columns))
            ->whereBetween('quotes.created_at', formated_date_range($this->dateRange, Quote::class))
            ->get();

        if ($data->count()) {
            return $data;
        } else {
            throw new \Exception('No data Found!');
        }
    }

    public function headings(): array
    {
        return array_map(
            fn ($col) => Str::title(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    public static function getColumns()
    {
        $quote = new Quote;
        $quote_columns = array_diff(
            Schema::getColumnListing($quote->getTable()),
            $quote->getHidden()
        );

        $quote_item = new QuoteItem;
        $quote_items_columns = array_diff(
            Schema::getColumnListing($quote_item->getTable()),
            $quote_item->getHidden()
        );

        return array_values(
            array_merge(
                array_map(fn ($col) => 'quotes.'.$col, $quote_columns),
                array_map(fn ($col) => 'quote_items.'.$col, $quote_items_columns)
            )
        );
    }
}
