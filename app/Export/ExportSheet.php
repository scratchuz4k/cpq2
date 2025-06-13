<?php

namespace App\Export;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    public function __construct(protected $provider, protected $collection, protected $columns){}

    public function collection()
    {
        return collect($this->collection);
    }

    public function headings(): array
    {
        return array_map(
            fn($col) => Str::title(str_replace('_', ' ', $col)),
            $this->columns
        );
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->provider;
    }
}
