<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductLinkImport extends Import
{
    public function collection(Collection $collection): void
    {
        foreach ($collection as $rowIndex => $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $item = $this->normalizeRow($row, $rowIndex);

            $this->results['processed']++;
            $this->results['data'][] = $item;

            if ($this->skipIfDuplicate($item, $rowIndex)) {
                continue;
            }

            $this->insertLink($item);
        }
    }

    protected function uniqueFields(): array
    {
        return ['product_sku', 'linked_product_sku'];
    }

    private function insertLink(array $row): bool
    {
        $productSku = $row['product_sku'] ?? null;
        $linkedSku = $row['linked_product_sku'] ?? null;

        $errors = [];

        if (empty($productSku)) {
            $errors[] = 'The Product SKU field is required.';
        }

        if (empty($linkedSku)) {
            $errors[] = 'The Linked Product SKU field is required.';
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if ($productSku === $linkedSku) {
            return $this->skip($row, ['A product cannot be linked to itself.']);
        }

        $product = Product::where('sku', $productSku)->first();
        $linkedProduct = Product::where('sku', $linkedSku)->first();

        if (! $product) {
            $errors[] = "No product found with SKU \"{$productSku}\".";
        }

        if (! $linkedProduct) {
            $errors[] = "No product found with SKU \"{$linkedSku}\".";
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if ($this->sharesStep($product->id, $linkedProduct->id)) {
            return $this->skip($row, 'The linked product cannot be added to the same step as the parent product.');
        }

        $exists = ProductLink::where('product_id', $product->id)->where('child_id', $linkedProduct->id)->exists();

        if ($exists) {
            return $this->skip($row, 'This product link already exists.');
        }

        if ($this->isDryRun) {
            $this->results['success']++;

            return true;
        }

        ProductLink::create([
            'product_id' => $product->id,
            'child_id' => $linkedProduct->id,
        ]);

        $this->results['success']++;

        return true;
    }

    /**
     * Mirrors ProductLinkCreateRequest's manual-creation rule: a linked
     * product cannot share a step with the parent product.
     */
    private function sharesStep(int $parentId, int $childId): bool
    {
        $parentStepIds = DB::table('product_step')->where('product_id', $parentId)->pluck('step_id');

        if ($parentStepIds->isEmpty()) {
            return false;
        }

        return DB::table('product_step')
            ->where('product_id', $childId)
            ->whereIn('step_id', $parentStepIds)
            ->exists();
    }

    public static function getImportTemplate(): array
    {
        $template = require app_path('Helpers/Import/productlink_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }
}
