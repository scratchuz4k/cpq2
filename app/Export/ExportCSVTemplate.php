<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportCSVTemplate implements FromCollection, ShouldAutoSize, WithCustomCsvSettings, WithHeadings, WithMapping
{
    protected array $fillable;

    public function __construct(
        protected string $model,
        protected Collection $collection,
        protected ?array $columns = null
    ) {
        $model = new $this->model;
        $this->fillable = getFillableColumns($model);
    }

    public function collection(): Collection
    {
        return $this->collection;
    }

    public function headings(): array
    {
        if ($this->collection->isNotEmpty() && $this->collection->every(fn ($item) => $item instanceof $this->model)) {
            return array_map(fn ($field) => Str::headline($field), $this->fillable);
        }

        if ($this->columns) {
            return array_values($this->columns);
        }

        return array_keys($this->collection->first());
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => app()->environment(['local', 'testing']) ? ';' : ',',
            'enclosure' => '',
            'line_ending' => "\r\n",
            'use_bom' => true,
        ];
    }

    public function map($row): array
    {
        if (is_array($row)) {
            return $row;
        }

        return array_map(
            fn (string $field) => $row->getAttribute($field),
            $this->fillable
        );
    }
}
