<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportCSVTemplate implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected string $model,
        protected Collection $collection
    ) {}

    public function collection(): Collection
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return array_keys($this->collection->first());
    }
}
