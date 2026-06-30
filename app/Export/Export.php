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
        $first = collect($this->collection->first())->keys()->all();

        return array_map(function ($col) {
            // if the key already contains spaces or uppercase it came from column_titles — use as-is
            if (str_contains($col, ' ') || preg_match('/[A-Z]/', $col)) {
                return $col;
            }

            return Str::title(str_replace('_', ' ', $col));
        }, $first);
    }
}
