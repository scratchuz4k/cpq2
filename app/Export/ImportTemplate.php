<?php

namespace App\Export;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportTemplate implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected string $model,
        protected Collection $collection
    ) {}

    public function collection(): Collection
    {
        return $this->collection->map(function ($product) {
            return [
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'supplier_id' => $product->supplier_id,
                'category_id' => $product->category_id,
                'status' => $product->status,
                'expires_at' => $product->expires_at,
                'price_code' => $product->price_code,
                'price_cost' => $product->price_cost,
                'price_net' => $product->price_net,
                'price_plan_id' => $product->price_plan_id,
                'parent_sku' => $product->parent_sku,
            ];
        });

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
            'price_code',
            'price_cost',
            'price_net',
            'price_plan_id',
            'parent_sku',
        ];
    }
}
