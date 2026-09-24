<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Support\Collection;

class ProductBundleImport extends Import
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
            $this->insertBundleItem($item);
        }
    }

    private function insertBundleItem(array $row): bool
    {
        // Column keys must match Str::slug($label, '_') of the CSV header label
        // (see app/Helpers/Import/productbundle_import.php's "columns"), which is
        // how Laravel Excel's WithHeadingRow derives row keys from the uploaded
        // file's header row — "Bundle Item Quantity" slugs to "bundle_item_quantity".
        $productSku = $row['product_sku'] ?? null;
        $itemSku = $row['bundle_item_sku'] ?? null;
        $quantity = $row['bundle_item_quantity'] ?? null;

        $errors = [];

        if (empty($productSku)) {
            $errors[] = 'The Product SKU field is required.';
        }

        if (empty($itemSku)) {
            $errors[] = 'The Bundle Item SKU field is required.';
        }

        if ($quantity === null) {
            $errors[] = 'The Bundle Item Quantity field is required.';
        } elseif (! ctype_digit((string) $quantity) || (int) $quantity < 1) {
            $errors[] = 'The Bundle Item Quantity must be a whole number of at least 1.';
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if ($productSku === $itemSku) {
            return $this->skip($row, ['A product cannot be a bundle item of itself.']);
        }

        $product = Product::where('sku', $productSku)->first();
        $itemProduct = Product::where('sku', $itemSku)->first();

        if (! $product) {
            $errors[] = "No product found with SKU \"{$productSku}\".";
        }

        if (! $itemProduct) {
            $errors[] = "No product found with SKU \"{$itemSku}\".";
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if (ProductBundle::where('product_id', $product->id)->where('item_id', $itemProduct->id)->exists()) {
            return $this->skip($row, 'This bundle item already exists.');
        }

        if ($this->isDryRun) {
            $this->results['success']++;

            return true;
        }

        ProductBundle::create([
            'product_id' => $product->id,
            'item_id' => $itemProduct->id,
            'quantity' => (int) $quantity,
        ]);

        $this->results['success']++;

        return true;
    }

    public static function getImportTemplate(): array
    {
        $template = require app_path('Helpers/Import/productbundle_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }
}
