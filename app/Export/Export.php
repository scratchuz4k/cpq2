<?php

namespace App\Export;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Export implements FromCollection, ShouldAutoSize, WithHeadings
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
        return array_map(
            fn ($col) => Str::title(str_replace('_', ' ', $col)),
            collect($this->collection->first())->keys()->all()
        );
    }
}
