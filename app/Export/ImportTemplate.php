<?php

namespace App\Export;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportTemplate implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected string $model,
        protected array $data
    ) {}

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'sku',
            'name',
            'description',
            'supplier_id',
            'category_id',
            'status',
            'expires_at',
            'price_plan_id',
            'parent_id',
        ];
    }
}
